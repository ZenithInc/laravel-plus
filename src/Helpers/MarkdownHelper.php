<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Helpers;

/**
 * Class MarkdownHelper
 * This class provides methods for generating Markdown based on the received data.
 */
class MarkdownHelper
{
    /**
     * @var string holds the generated Markdown code.
     */
    private string $context = '';

    /**
     * @var string holds the markdown meta info.
     */
    private string $meta = '';

    /**
     * This function allows to add meta information to markdown.
     *
     * @param  array  $items  associative array with meta keys and their values
     * @return self An instance of this class
     */
    public function meta(array $items): self
    {
        $this->meta = '---'.PHP_EOL;
        foreach ($items as $key => $value) {
            $this->meta .= "$key: $value".PHP_EOL;
        }
        $this->meta .= '---'.PHP_EOL;

        return $this;
    }

    /**
     * Method for creating a level-1 heading in Markdown.
     *
     * @param  string  $title  The title of the heading.
     * @return self Returns the instance of the class.
     */
    public function h1(string $title): self
    {
        $this->context .= "# $title #".PHP_EOL;

        return $this;
    }

    /**
     * Method for creating a level-2 heading in Markdown.
     *
     * @param  string  $title  The title of the heading.
     * @return self Returns the instance of the class.
     */
    public function h2(string $title): self
    {
        $this->context .= "## $title ##".PHP_EOL;

        return $this;
    }

    /**
     * Method for creating a level-3 heading in Markdown.
     *
     * @param  string  $title  The title of the heading.
     * @return self Returns the instance of the class.
     */
    public function h3(string $title): self
    {
        $this->context .= "### $title ###".PHP_EOL;

        return $this;
    }

    /**
     * Method for creating a level-4 heading in Markdown.
     *
     * @param  string  $title  The title of the heading.
     * @return self Returns the instance of the class.
     */
    public function h4(string $title): self
    {
        $this->context .= "#### $title ####".PHP_EOL;

        return $this;
    }

    /**
     * Method for creating a level-5 heading in Markdown.
     *
     * @param  string  $title  The title of the heading.
     * @return self Returns the instance of the class.
     */
    public function h5(string $title): self
    {
        $this->context .= "##### $title #####".PHP_EOL;

        return $this;
    }

    /**
     * Method for creating a level-6 heading in Markdown.
     *
     * @param  string  $title  The title of the heading.
     * @return self Returns the instance of the class.
     */
    public function h6(string $title): self
    {
        $this->context .= "###### $title ######".PHP_EOL;

        return $this;
    }

    /**
     * Method for creating a new paragraph in Markdown.
     *
     * @param  string  $context  The content of the paragraph.
     * @return self Returns the instance of the class.
     */
    public function p(string $context): self
    {
        $this->context .= $context.PHP_EOL;

        return $this;
    }

    /**
     * Method for creating a new inline-code in Markdown.
     *
     * @return self Returns the instance of the class.
     */
    public function inlineCode(string $code): self
    {
        $this->context .= " `$code` ";

        return $this;
    }

    /**
     * Method for creating a new code in Markdown.
     *
     * @return self Returns the instance of the class.
     */
    public function code(string $code, string $language = 'plain'): self
    {
        $this->context .= PHP_EOL."```$language".PHP_EOL;
        $this->context .= $code.PHP_EOL;
        $this->context .= '```'.PHP_EOL;

        return $this;
    }

    /**
     * This function is used to add a horizontal rule to the markdown context.
     *
     * @return self Returns the instance of the class to support method chaining.
     */
    public function hr(): self
    {
        $this->context .= PHP_EOL.'-------'.PHP_EOL;

        return $this;
    }

    /**
     * Generate a Markdown table
     *
     * @param  array  $headers  Array of table headers
     * @param  array  $rows  Array of arrays where each array contains a row of table cells
     */
    public function table(array $headers, array $rows): self
    {
        $this->context .= PHP_EOL;
        $this->context .= '| '.implode(' | ', $headers).' |'.PHP_EOL;
        $this->context .= str_repeat('|---', count($headers)).'|'.PHP_EOL;
        foreach ($rows as $row) {
            $this->context .= '| '.implode(' | ', $row).' |'.PHP_EOL;
        }
        $this->context .= PHP_EOL;

        return $this;
    }

    /**
     * This function allows adding additional context/info and marks it as a warning in the Markdown format.
     *
     * @param  string  $context  - A string that represents the context/info to be added.
     * @return self - Returns $this for method chaining purposes.
     */
    public function warning(string $context): self
    {
        $this->context .= "> $context".PHP_EOL;

        return $this;
    }

    /**
     * Turns a string into bold text in Markdown format.
     *
     * @param  string  $context  The text that needs to be bolded.
     * @return self Returns the instance of the class itself for chaining.
     */
    public function bold(string $context): self
    {
        $this->context = "**$context**";

        return $this;
    }

    /**
     * Method for creating a new li in Markdown.
     *
     * @return $this
     */
    public function li(array $items): self
    {
        $i = 0;
        foreach ($items as $item) {
            $this->context .= (++$i).$item.PHP_EOL;
        }

        return $this;
    }

    /**
     * Method for creating a new ol in Markdown.
     *
     * @return $this
     */
    public function ol(array $items): self
    {
        foreach ($items as $item) {
            $this->context .= "* $item".PHP_EOL;
        }

        return $this;
    }

    /**
     * Generates an image Markdown string and appends it to the current context.
     *
     * @param  string  $url  The URL of the image.
     * @param  string  $alt  The alternative text for the image. Default value is an empty string.
     * @return self Returns the current instance of MarkdownHelper to allow method chaining.
     */
    public function image(string $url, string $alt = ''): self
    {
        $this->context .= PHP_EOL."![$alt]($url)".PHP_EOL;

        return $this;
    }

    /**
     * Adds a Markdown link to the context.
     *
     * @param  string  $text  Text to be displayed as a link.
     * @param  string  $url  URL of the link.
     * @return self Returns instance of the current class.
     */
    public function link(string $text, string $url): self
    {
        $this->context .= " [$text]($url) ".PHP_EOL;

        return $this;
    }

    /**
     * Method to get the generated Markdown.
     *
     * @return string The generated Markdown.
     */
    public function build(): string
    {
        return $this->meta.PHP_EOL.$this->context;
    }
}