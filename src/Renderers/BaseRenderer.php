<?php

namespace EthanJenkins\LivewireEditorjs\Renderers;

abstract class BaseRenderer {
    public static function render(array $data): string {
        $blocks = [];

        foreach ($data['blocks'] ?? [] as $block) {
            $rendered = match ($block['type'] ?? null) {
                'paragraph' => static::renderParagraph($block['data'] ?? []),
                'header' => static::renderHeader($block['data'] ?? []),
                'image' => static::renderImage($block['data'] ?? []),
                'delimiter' => static::renderDelimiter(),
                'list' => static::renderList($block['data'] ?? []),
                'quote' => static::renderQuote($block['data'] ?? []),
                'warning' => static::renderWarning($block['data'] ?? []),
                'table' => static::renderTable($block['data'] ?? []),
                default => '',
            };

            if ($rendered !== '') {
                $blocks[] = static::wrapBlock($rendered);
            }
        }

        return static::finalize(implode('', $blocks));
    }

    protected static function wrapBlock(string $html): string {
        return '<div class="mb-4">' . $html . '</div>';
    }

    protected static function finalize(string $html): string {
        return $html;
    }

    protected static function sanitizeInline(string $text): string {
        return strip_tags($text, '<b><strong><i><em><u><a><mark><code><br>');
    }

    abstract protected static function renderParagraph(array $data): string;
    abstract protected static function renderHeader(array $data): string;
    abstract protected static function renderImage(array $data): string;
    abstract protected static function renderDelimiter(): string;
    abstract protected static function renderList(array $data): string;
    abstract protected static function renderQuote(array $data): string;
    abstract protected static function renderWarning(array $data): string;
    abstract protected static function renderTable(array $data): string;
}
