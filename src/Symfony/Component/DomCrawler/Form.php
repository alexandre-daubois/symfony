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

use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FormField;

/**
 * Form represents an HTML form.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Form extends Link implements \ArrayAccess
{
    use FormTrait;

    private \DOMElement $button;
    private FormFieldRegistry $fields;

    /**
     * @param \DOMElement $node       A \DOMElement instance
     * @param string|null $currentUri The URI of the page where the form is embedded
     * @param string|null $method     The method to use for the link (if null, it defaults to the method defined by the form)
     * @param string|null $baseHref   The URI of the <base> used for relative links, but not for empty action
     *
     * @throws \LogicException if the node is not a button inside a form tag
     */
    public function __construct(
        \DOMElement $node,
        ?string $currentUri = null,
        ?string $method = null,
        private ?string $baseHref = null,
    ) {
        parent::__construct($node, $currentUri, $method);

        $this->initialize();
    }

    /**
     * Gets the form node associated with this form.
     */
    public function getFormNode(): \DOMElement
    {
        return $this->node;
    }

    /**
     * Gets the field values.
     *
     * The returned array does not include file fields (@see getFiles).
     */
    public function getValues(): array
    {
        $values = [];
        foreach ($this->fields->all() as $name => $field) {
            if ($field->isDisabled()) {
                continue;
            }

            if (!$field instanceof Field\FileFormField && $field->hasValue()) {
                $values[$name] = $field->getValue();
            }
        }

        return $values;
    }

    /**
     * Gets the file field values.
     */
    public function getFiles(): array
    {
        if (!\in_array($this->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return [];
        }

        $files = [];

        foreach ($this->fields->all() as $name => $field) {
            if ($field->isDisabled()) {
                continue;
            }

            if ($field instanceof Field\FileFormField) {
                $files[$name] = $field->getValue();
            }
        }

        return $files;
    }

    /**
     * Gets the form name.
     *
     * If no name is defined on the form, an empty string is returned.
     */
    public function getName(): string
    {
        return $this->node->getAttribute('name');
    }

    /**
     * Gets a named field.
     *
     * @return FormField|FormField[]|FormField[][]
     *
     * @throws \InvalidArgumentException When field is not present in this form
     */
    public function get(string $name): FormField|array
    {
        return $this->fields->get($name);
    }

    /**
     * Sets a named field.
     */
    public function set(FormField $field): void
    {
        $this->fields->add($field);
    }

    /**
     * Gets the value of a field.
     *
     * @param string $name The field name
     *
     * @return FormField|FormField[]|FormField[][]
     *
     * @throws \InvalidArgumentException if the field does not exist
     */
    public function offsetGet(mixed $name): FormField|array
    {
        return $this->fields->get($name);
    }

    /**
     * Disables validation.
     *
     * @return $this
     */
    public function disableValidation(): static
    {
        foreach ($this->fields->all() as $field) {
            if ($field instanceof ChoiceFormField) {
                $field->disableValidation();
            }
        }

        return $this;
    }

    /**
     * Sets the node for the form.
     *
     * Expects a 'submit' button \DOMElement and finds the corresponding form element, or the form element itself.
     *
     * @throws \LogicException If given node is not a button or input or does not have a form ancestor
     */
    protected function setNode(\DOMElement $node): void
    {
        $this->button = $node;
        if ('button' === $node->nodeName || ('input' === $node->nodeName && \in_array(strtolower($node->getAttribute('type')), ['submit', 'button', 'image']))) {
            if ($node->hasAttribute('form')) {
                // if the node has the HTML5-compliant 'form' attribute, use it
                $formId = $node->getAttribute('form');
                $form = $node->ownerDocument->getElementById($formId);
                if (null === $form) {
                    throw new \LogicException(\sprintf('The selected node has an invalid form attribute (%s).', $formId));
                }
                $this->node = $form;

                return;
            }
            // we loop until we find a form ancestor
            do {
                if (null === $node = $node->parentNode) {
                    throw new \LogicException('The selected node does not have a form ancestor.');
                }
            } while ('form' !== $node->nodeName);
        } elseif ('form' !== $node->nodeName) {
            throw new \LogicException(\sprintf('Unable to submit on a "%s" tag.', $node->nodeName));
        }

        $this->node = $node;
    }

    /**
     * Adds form elements related to this form.
     *
     * Creates an internal copy of the submitted 'button' element and
     * the form node or the entire document depending on whether we need
     * to find non-descendant elements through HTML5 'form' attribute.
     */
    private function initialize(): void
    {
        $this->fields = new FormFieldRegistry();

        $xpath = new \DOMXPath($this->node->ownerDocument);

        // add submitted button if it has a valid name
        if ('form' !== $this->button->nodeName && $this->button->hasAttribute('name') && $this->button->getAttribute('name')) {
            if ('input' == $this->button->nodeName && 'image' == strtolower($this->button->getAttribute('type'))) {
                $name = $this->button->getAttribute('name');
                $this->button->setAttribute('value', '0');

                // temporarily change the name of the input node for the x coordinate
                $this->button->setAttribute('name', $name.'.x');
                $this->set(new Field\InputFormField($this->button));

                // temporarily change the name of the input node for the y coordinate
                $this->button->setAttribute('name', $name.'.y');
                $this->set(new Field\InputFormField($this->button));

                // restore the original name of the input node
                $this->button->setAttribute('name', $name);
            } else {
                $this->set(new Field\InputFormField($this->button));
            }
        }

        // find form elements corresponding to the current form
        if ($this->node->hasAttribute('id')) {
            // corresponding elements are either descendants or have a matching HTML5 form attribute
            $formId = Crawler::xpathLiteral($this->node->getAttribute('id'));

            $fieldNodes = $xpath->query(\sprintf('( descendant::input[@form=%s] | descendant::button[@form=%1$s] | descendant::textarea[@form=%1$s] | descendant::select[@form=%1$s] | //form[@id=%1$s]//input[not(@form)] | //form[@id=%1$s]//button[not(@form)] | //form[@id=%1$s]//textarea[not(@form)] | //form[@id=%1$s]//select[not(@form)] )[( not(ancestor::template) or ancestor::turbo-stream )]', $formId));
            foreach ($fieldNodes as $node) {
                $this->addField($node);
            }
        } else {
            // do the xpath query with $this->node as the context node, to only find descendant elements
            // however, descendant elements with form attribute are not part of this form
            $fieldNodes = $xpath->query('( descendant::input[not(@form)] | descendant::button[not(@form)] | descendant::textarea[not(@form)] | descendant::select[not(@form)] )[( not(ancestor::template) or ancestor::turbo-stream )]', $this->node);
            foreach ($fieldNodes as $node) {
                $this->addField($node);
            }
        }

        if ($this->baseHref && '' !== $this->node->getAttribute('action')) {
            $this->currentUri = $this->baseHref;
        }
    }

    private function addField(\DOMElement $node): void
    {
        if (!$node->hasAttribute('name') || !$node->getAttribute('name')) {
            return;
        }

        $nodeName = $node->nodeName;
        if ('select' == $nodeName || 'input' == $nodeName && 'checkbox' == strtolower($node->getAttribute('type'))) {
            $this->set(new ChoiceFormField($node));
        } elseif ('input' == $nodeName && 'radio' == strtolower($node->getAttribute('type'))) {
            // there may be other fields with the same name that are no choice
            // fields already registered (see https://github.com/symfony/symfony/issues/11689)
            if ($this->has($node->getAttribute('name')) && $this->get($node->getAttribute('name')) instanceof ChoiceFormField) {
                $this->get($node->getAttribute('name'))->addChoice($node);
            } else {
                $this->set(new ChoiceFormField($node));
            }
        } elseif ('input' == $nodeName && 'file' == strtolower($node->getAttribute('type'))) {
            $this->set(new Field\FileFormField($node));
        } elseif ('input' == $nodeName && !\in_array(strtolower($node->getAttribute('type')), ['submit', 'button', 'image'])) {
            $this->set(new Field\InputFormField($node));
        } elseif ('textarea' == $nodeName) {
            $this->set(new Field\TextareaFormField($node));
        }
    }
}
