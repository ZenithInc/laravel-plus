<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Helpers;

use Illuminate\Support\Str;

/**
 * Helper class for VitePress Configurations
 */
class VitePressConfigHelper
{
    /** @var array Base configuration array */
    private array $config = [
        'title' => 'The AI-Next Project API',
        'description' => 'The api docs',
        'nav' => [],
        'sidebar' => [],
    ];

    /** @var string Template for vitepress config */
    private string $template;

    /**
     * VitePressConfigHelper constructor
     * Initializes the template string
     */
    public function __construct()
    {
        /*Initialize the template for configuration*/
        $this->template = <<<'EOT'
        import { defineConfig } from 'vitepress'
        export default defineConfig({
            title: "{{title}}",
            description: "{{description}}",
            themeConfig: {
                nav: [
        {{nav}}
                ],
                sidebar: [
        {{sidebar}}
                ]
            }
        })
        EOT;
    }

    /**
     * Define navigation items
     *
     * @return $this
     */
    public function nav(string $text, string $link): self
    {
        /*Add navigation item to the configuration*/
        foreach ($this->config['nav'] as $item) {
            if ($item['text'] === $text) {
                return $this;
            }
        }
        $this->config['nav'][] = compact('text', 'link');

        return $this;
    }

    /**
     * Define sidebar items
     *
     * @return $this
     */
    public function sidebar(string $text): self
    {
        $items = [];
        $this->config['sidebar'][] = compact('text', 'items');

        return $this;
    }

    /**
     * Append sidebar items
     *
     * @return $this
     */
    public function sidebarAppendItem(string $sidebar, string $text, string $link): self
    {
        /*Append item to the specified sidebar in the configuration*/
        foreach ($this->config['sidebar'] as &$bar) {
            if ($bar['text'] != $sidebar) {
                continue;
            }
            $bar['items'][] = compact('text', 'link');
        }

        return $this;
    }

    /**
     * Replace the placeholders in the template with actual values
     */
    private function replace(): void
    {
        /*Replace placeholders in the template with actual values from configuration*/
        $this->template = Str::replace('{{title}}', $this->config['title'], $this->template);
        $this->template = Str::replace('{{description}}', $this->config['description'], $this->template);
        $navItems = '';
        foreach ($this->config['nav'] as $nav) {
            $navItems .= "\t\t\t{ text: '".$nav['text']."', link: '".$nav['link']."' },".PHP_EOL;
        }
        $this->template = Str::replace('{{nav}}', $navItems, $this->template);
        $sidebar = '';
        foreach ($this->config['sidebar'] as $bar) {
            $sidebar .= "\t\t\t{".PHP_EOL;
            $sidebar .= "\t\t\t\ttext: '".$bar['text']."',".PHP_EOL;
            $sidebar .= "\t\t\t\titems: [".PHP_EOL;
            foreach ($bar['items'] as $item) {
                $sidebar .= "\t\t\t\t\t{ text: '".$item['text']."', link: '".$item['link']."' },".PHP_EOL;
            }
            $sidebar .= "\t\t\t\t]".PHP_EOL;
            $sidebar .= "\t\t\t},";
        }
        $this->template = Str::replace('{{sidebar}}', $sidebar, $this->template);
    }

    /**
     * Build the VitePress config
     */
    public function build(): string
    {
        /*Build the final configuration by replacing placeholders*/
        $this->replace();

        return $this->template;
    }
}