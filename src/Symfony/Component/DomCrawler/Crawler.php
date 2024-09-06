<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DomCrawler;

use Masterminds\HTML5;
use Symfony\Component\CssSelector\CssSelectorConverter;

/**
 * Crawler eases navigation of a list of \DOMNode objects.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @implements \IteratorAggregate<int, \DOMNode>
 */
class Crawler implements \Countable, \IteratorAggregate
{
    use CrawlerTrait;

    private ?\DOMDocument $document = null;

    /**
     * @var list<\DOMNode>
     */
    private array $nodes = [];

    private ?HTML5 $html5Parser;

    /**
     * @param \DOMNodeList|\DOMNode|\DOMNode[]|string|null $node A Node to use as the base for the crawling
     */
    public function __construct(
        \DOMNodeList|\DOMNode|array|string|null $node = null,
        protected ?string $uri = null,
        ?string $baseHref = null,
        bool $useHtml5Parser = true,
    ) {
        $this->baseHref = $baseHref ?: $uri;
        $this->html5Parser = $useHtml5Parser ? new HTML5(['disable_html_ns' => true]) : null;
        $this->cachedNamespaces = new \ArrayObject();

        $this->add($node);
    }

    /**
     * Adds a node to the current list of nodes.
     *
     * This method uses the appropriate specialized add*() method based
     * on the type of the argument.
     *
     * @param \DOMNodeList|\DOMNode|\DOMNode[]|string|null $node A node
     *
     * @throws \InvalidArgumentException when node is not the expected type
     */
    public function add(\DOMNodeList|\DOMNode|array|string|null $node): void
    {
        if ($node instanceof \DOMNodeList) {
            $this->addNodeList($node);
        } elseif ($node instanceof \DOMNode) {
            $this->addNode($node);
        } elseif (\is_array($node)) {
            $this->addNodes($node);
        } elseif (\is_string($node)) {
            $this->addContent($node);
        } elseif (null !== $node) {
            throw new \InvalidArgumentException(\sprintf('Expecting a DOMNodeList or DOMNode instance, an array, a string, or null, but got "%s".', get_debug_type($node)));
        }
    }

    /**
     * Adds an HTML content to the list of nodes.
     *
     * The libxml errors are disabled when the content is parsed.
     *
     * If you want to get parsing errors, be sure to enable
     * internal errors via libxml_use_internal_errors(true)
     * and then, get the errors via libxml_get_errors(). Be
     * sure to clear errors with libxml_clear_errors() afterward.
     */
    public function addHtmlContent(string $content, string $charset = 'UTF-8'): void
    {
        $dom = $this->parseHtmlString($content, $charset);
        $this->addDocument($dom);

        $base = $this->filterRelativeXPath('descendant-or-self::base')->extract(['href']);

        $baseHref = current($base);
        if (\count($base) && !empty($baseHref)) {
            if ($this->baseHref) {
                $linkNode = $dom->createElement('a');
                $linkNode->setAttribute('href', $baseHref);
                $link = new Link($linkNode, $this->baseHref);
                $this->baseHref = $link->getUri();
            } else {
                $this->baseHref = $baseHref;
            }
        }
    }

    /**
     * Adds an XML content to the list of nodes.
     *
     * The libxml errors are disabled when the content is parsed.
     *
     * If you want to get parsing errors, be sure to enable
     * internal errors via libxml_use_internal_errors(true)
     * and then, get the errors via libxml_get_errors(). Be
     * sure to clear errors with libxml_clear_errors() afterward.
     *
     * @param int $options Bitwise OR of the libxml option constants
     *                     LIBXML_PARSEHUGE is dangerous, see
     *                     http://symfony.com/blog/security-release-symfony-2-0-17-released
     */
    public function addXmlContent(string $content, string $charset = 'UTF-8', int $options = \LIBXML_NONET): void
    {
        // remove the default namespace if it's the only namespace to make XPath expressions simpler
        if (!str_contains($content, 'xmlns:')) {
            $content = str_replace('xmlns', 'ns', $content);
        }

        $internalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', $charset);
        $dom->validateOnParse = true;

        if ('' !== trim($content)) {
            @$dom->loadXML($content, $options);
        }

        libxml_use_internal_errors($internalErrors);

        $this->addDocument($dom);

        $this->isHtml = false;
    }

    /**
     * Adds a \DOMDocument to the list of nodes.
     *
     * @param \DOMDocument $dom A \DOMDocument instance
     */
    public function addDocument(\DOMDocument $dom): void
    {
        if ($dom->documentElement) {
            $this->addNode($dom->documentElement);
        }
    }

    /**
     * Adds a \DOMNodeList to the list of nodes.
     *
     * @param \DOMNodeList $nodes A \DOMNodeList instance
     */
    public function addNodeList(\DOMNodeList $nodes): void
    {
        foreach ($nodes as $node) {
            if ($node instanceof \DOMNode) {
                $this->addNode($node);
            }
        }
    }

    /**
     * Adds an array of \DOMNode instances to the list of nodes.
     *
     * @param \DOMNode[] $nodes An array of \DOMNode instances
     */
    public function addNodes(array $nodes): void
    {
        foreach ($nodes as $node) {
            $this->add($node);
        }
    }

    /**
     * Adds a \DOMNode instance to the list of nodes.
     *
     * @param \DOMNode $node A \DOMNode instance
     */
    public function addNode(\DOMNode $node): void
    {
        if ($node instanceof \DOMDocument) {
            $node = $node->documentElement;
        }

        if (null !== $this->document && $this->document !== $node->ownerDocument) {
            throw new \InvalidArgumentException('Attaching DOM nodes from multiple documents in the same crawler is forbidden.');
        }

        $this->document ??= $node->ownerDocument;

        // Don't add duplicate nodes in the Crawler
        if (\in_array($node, $this->nodes, true)) {
            return;
        }

        $this->nodes[] = $node;
    }

    /**
     * Returns the text of the first node of the list.
     *
     * Pass true as the second argument to normalize whitespaces.
     *
     * @param string|null $default             When not null: the value to return when the current node is empty
     * @param bool        $normalizeWhitespace Whether whitespaces should be trimmed and normalized to single spaces
     *
     * @throws \InvalidArgumentException When current node is empty
     */
    public function text(?string $default = null, bool $normalizeWhitespace = true): string
    {
        if (!$this->nodes) {
            if (null !== $default) {
                return $default;
            }

            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $text = $this->getNode(0)->nodeValue;

        if ($normalizeWhitespace) {
            return $this->normalizeWhitespace($text);
        }

        return $text;
    }

    /**
     * Returns only the inner text that is the direct descendent of the current node, excluding any child nodes.
     *
     * @param bool $normalizeWhitespace Whether whitespaces should be trimmed and normalized to single spaces
     */
    public function innerText(bool $normalizeWhitespace = true): string
    {
        foreach ($this->getNode(0)->childNodes as $childNode) {
            if (\XML_TEXT_NODE !== $childNode->nodeType && \XML_CDATA_SECTION_NODE !== $childNode->nodeType) {
                continue;
            }
            if (!$normalizeWhitespace) {
                return $childNode->nodeValue;
            }
            if ('' !== trim($childNode->nodeValue)) {
                return $this->normalizeWhitespace($childNode->nodeValue);
            }
        }

        return '';
    }

    /**
     * Returns the first node of the list as HTML.
     *
     * @param string|null $default When not null: the value to return when the current node is empty
     *
     * @throws \InvalidArgumentException When current node is empty
     */
    public function html(?string $default = null): string
    {
        if (!$this->nodes) {
            if (null !== $default) {
                return $default;
            }

            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);
        $owner = $node->ownerDocument;

        if ($this->html5Parser && '<!DOCTYPE html>' === $owner->saveXML($owner->childNodes[0])) {
            $owner = $this->html5Parser;
        }

        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $owner->saveHTML($child);
        }

        return $html;
    }

    public function outerHtml(): string
    {
        if (!\count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);
        $owner = $node->ownerDocument;

        if ($this->html5Parser && '<!DOCTYPE html>' === $owner->saveXML($owner->childNodes[0])) {
            $owner = $this->html5Parser;
        }

        return $owner->saveHTML($node);
    }

    /**
     * Evaluates an XPath expression.
     *
     * Since an XPath expression might evaluate to either a simple type or a \DOMNodeList,
     * this method will return either an array of simple types or a new Crawler instance.
     */
    public function evaluate(string $xpath): array|self
    {
        if (null === $this->document) {
            throw new \LogicException('Cannot evaluate the expression on an uninitialized crawler.');
        }

        $data = [];
        $domxpath = $this->createDOMXPath($this->document, $this->findNamespacePrefixes($xpath));

        foreach ($this->nodes as $node) {
            $data[] = $domxpath->evaluate($xpath, $node);
        }

        if (isset($data[0]) && $data[0] instanceof \DOMNodeList) {
            return $this->createSubCrawler($data);
        }

        return $data;
    }

    /**
     * Extracts information from the list of nodes.
     *
     * You can extract attributes or/and the node value (_text).
     *
     * Example:
     *
     *     $crawler->filter('h1 a')->extract(['_text', 'href']);
     */
    public function extract(array $attributes): array
    {
        $count = \count($attributes);

        $data = [];
        foreach ($this->nodes as $node) {
            $elements = [];
            foreach ($attributes as $attribute) {
                if ('_text' === $attribute) {
                    $elements[] = $node->nodeValue;
                } elseif ('_name' === $attribute) {
                    $elements[] = $node->nodeName;
                } else {
                    $elements[] = $node->getAttribute($attribute);
                }
            }

            $data[] = 1 === $count ? $elements[0] : $elements;
        }

        return $data;
    }

    /**
     * Returns a Link object for the first node in the list.
     *
     * @throws \InvalidArgumentException If the current node list is empty or the selected node is not instance of DOMElement
     */
    public function link(string $method = 'get'): Link
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);

        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException(\sprintf('The selected node should be instance of DOMElement, got "%s".', get_debug_type($node)));
        }

        return new Link($node, $this->baseHref, $method);
    }

    /**
     * Returns an array of Link objects for the nodes in the list.
     *
     * @return Link[]
     *
     * @throws \InvalidArgumentException If the current node list contains non-DOMElement instances
     */
    public function links(): array
    {
        $links = [];
        foreach ($this->nodes as $node) {
            if (!$node instanceof \DOMElement) {
                throw new \InvalidArgumentException(\sprintf('The current node list should contain only DOMElement instances, "%s" found.', get_debug_type($node)));
            }

            $links[] = new Link($node, $this->baseHref, 'get');
        }

        return $links;
    }

    /**
     * Returns an Image object for the first node in the list.
     *
     * @throws \InvalidArgumentException If the current node list is empty
     */
    public function image(): Image
    {
        if (!\count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);

        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException(\sprintf('The selected node should be instance of DOMElement, got "%s".', get_debug_type($node)));
        }

        return new Image($node, $this->baseHref);
    }

    /**
     * Returns an array of Image objects for the nodes in the list.
     *
     * @return Image[]
     */
    public function images(): array
    {
        $images = [];
        foreach ($this as $node) {
            if (!$node instanceof \DOMElement) {
                throw new \InvalidArgumentException(\sprintf('The current node list should contain only DOMElement instances, "%s" found.', get_debug_type($node)));
            }

            $images[] = new Image($node, $this->baseHref);
        }

        return $images;
    }

    /**
     * Returns a Form object for the first node in the list.
     *
     * @throws \InvalidArgumentException If the current node list is empty or the selected node is not instance of DOMElement
     */
    public function form(?array $values = null, ?string $method = null): Form
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);

        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException(\sprintf('The selected node should be instance of DOMElement, got "%s".', get_debug_type($node)));
        }

        $form = new Form($node, $this->uri, $method, $this->baseHref);

        if (null !== $values) {
            $form->setValues($values);
        }

        return $form;
    }

    public function getNode(int $position): ?\DOMNode
    {
        return $this->nodes[$position] ?? null;
    }

    public function count(): int
    {
        return \count($this->nodes);
    }

    /**
     * @return \ArrayIterator<int, \DOMNode>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->nodes);
    }

    protected function sibling(\DOMNode $node, string $siblingDir = 'nextSibling'): array
    {
        $nodes = [];

        $currentNode = $this->getNode(0);
        do {
            if ($node !== $currentNode && \XML_ELEMENT_NODE === $node->nodeType) {
                $nodes[] = $node;
            }
        } while ($node = $node->$siblingDir);

        return $nodes;
    }

    private function parseHtml5(string $htmlContent, string $charset = 'UTF-8'): \DOMDocument
    {
        if (!$this->supportsEncoding($charset)) {
            $htmlContent = $this->convertToHtmlEntities($htmlContent, $charset);
            $charset = 'UTF-8';
        }

        return $this->html5Parser->parse($htmlContent, ['encoding' => $charset]);
    }

    private function supportsEncoding(string $encoding): bool
    {
        try {
            return '' === @mb_convert_encoding('', $encoding, 'UTF-8');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function parseXhtml(string $htmlContent, string $charset = 'UTF-8'): \DOMDocument
    {
        if ('UTF-8' === $charset && preg_match('//u', $htmlContent)) {
            $htmlContent = '<?xml encoding="UTF-8">'.$htmlContent;
        } else {
            $htmlContent = $this->convertToHtmlEntities($htmlContent, $charset);
        }

        $internalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', $charset);
        $dom->validateOnParse = true;

        if ('' !== trim($htmlContent)) {
            @$dom->loadHTML($htmlContent);
        }

        libxml_use_internal_errors($internalErrors);

        return $dom;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function createDOMXPath(\DOMDocument $document, array $prefixes = []): \DOMXPath
    {
        $domxpath = new \DOMXPath($document);

        foreach ($prefixes as $prefix) {
            $namespace = $this->discoverNamespace($domxpath, $prefix);
            if (null !== $namespace) {
                $domxpath->registerNamespace($prefix, $namespace);
            }
        }

        return $domxpath;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function discoverNamespace(\DOMXPath $domxpath, string $prefix): ?string
    {
        if (\array_key_exists($prefix, $this->namespaces)) {
            return $this->namespaces[$prefix];
        }

        if ($this->cachedNamespaces->offsetExists($prefix)) {
            return $this->cachedNamespaces[$prefix];
        }

        // ask for one namespace, otherwise we'd get a collection with an item for each node
        $namespaces = $domxpath->query(\sprintf('(//namespace::*[name()="%s"])[last()]', $this->defaultNamespacePrefix === $prefix ? '' : $prefix));

        return $this->cachedNamespaces[$prefix] = ($node = $namespaces->item(0)) ? $node->nodeValue : null;
    }

    /**
     * Creates a crawler for some subnodes.
     *
     * @param \DOMNodeList|\DOMNode|\DOMNode[]|string|null $nodes
     */
    private function createSubCrawler(\DOMNodeList|\DOMNode|array|string|null $nodes): static
    {
        $crawler = new static($nodes, $this->uri, $this->baseHref);
        $crawler->isHtml = $this->isHtml;
        $crawler->document = $this->document;
        $crawler->namespaces = $this->namespaces;
        $crawler->cachedNamespaces = $this->cachedNamespaces;
        $crawler->html5Parser = $this->html5Parser;

        return $crawler;
    }

    /**
     * Parse string into DOMDocument object using HTML5 parser if the content is HTML5 and the library is available.
     * Use libxml parser otherwise.
     */
    private function parseHtmlString(string $content, string $charset): \DOMDocument
    {
        if ($this->canParseHtml5String($content)) {
            return $this->parseHtml5($content, $charset);
        }

        return $this->parseXhtml($content, $charset);
    }

    private function canParseHtml5String(string $content): bool
    {
        if (!$this->html5Parser) {
            return false;
        }

        if (false === ($pos = stripos($content, '<!doctype html>'))) {
            return false;
        }

        $header = substr($content, 0, $pos);

        return '' === $header || $this->isValidHtml5Heading($header);
    }
}
