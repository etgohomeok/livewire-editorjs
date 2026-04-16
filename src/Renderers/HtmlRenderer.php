<?php

namespace EthanJenkins\LivewireEditorjs\Renderers;

class HtmlRenderer extends BaseRenderer {
    protected static function wrapBlock(string $html): string {
        return $html;
    }

    protected static function renderParagraph(array $data): string {
        return '<p>' . static::sanitizeInline($data['text'] ?? '') . '</p>';
    }

    protected static function renderHeader(array $data): string {
        $level = max(1, min(6, (int) ($data['level'] ?? 2)));
        $text = static::sanitizeInline($data['text'] ?? '');

        return "<h{$level}>{$text}</h{$level}>";
    }

    protected static function renderImage(array $data): string {
        $url = e($data['file']['url'] ?? '');
        if ($url === '') {
            return '';
        }

        $caption = $data['caption'] ?? '';
        $alt = e(strip_tags($caption));

        $html = "<figure><img src=\"{$url}\" alt=\"{$alt}\">";
        if ($caption !== '') {
            $html .= '<figcaption>' . static::sanitizeInline($caption) . '</figcaption>';
        }
        $html .= '</figure>';

        return $html;
    }

    protected static function renderDelimiter(): string {
        return '<hr>';
    }

    protected static function renderList(array $data): string {
        $style = $data['style'] ?? 'unordered';
        $items = $data['items'] ?? [];

        if ($style === 'checklist') {
            return static::renderChecklist($items);
        }

        $tag = $style === 'ordered' ? 'ol' : 'ul';

        return "<{$tag}>" . static::renderListItems($items, $tag) . "</{$tag}>";
    }

    protected static function renderListItems(array $items, string $tag): string {
        $html = '';
        foreach ($items as $item) {
            $html .= '<li>' . static::sanitizeInline($item['content'] ?? '');
            if (!empty($item['items'])) {
                $html .= "<{$tag}>" . static::renderListItems($item['items'], $tag) . "</{$tag}>";
            }
            $html .= '</li>';
        }
        return $html;
    }

    protected static function renderChecklist(array $items): string {
        $html = '<ul>';
        foreach ($items as $item) {
            $checked = !empty($item['meta']['checked']) ? ' checked' : '';
            $html .= '<li><input type="checkbox" disabled' . $checked . '> ' . static::sanitizeInline($item['content'] ?? '');
            if (!empty($item['items'])) {
                $html .= static::renderChecklist($item['items']);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    protected static function renderQuote(array $data): string {
        $text = static::sanitizeInline($data['text'] ?? '');
        $caption = $data['caption'] ?? '';

        $html = '<blockquote><p>' . $text . '</p>';
        if ($caption !== '') {
            $html .= '<footer>' . static::sanitizeInline($caption) . '</footer>';
        }
        $html .= '</blockquote>';

        return $html;
    }

    protected static function renderWarning(array $data): string {
        $title = e($data['title'] ?? '');
        $message = e($data['message'] ?? '');

        $html = '<aside class="warning">';
        if ($title !== '') {
            $html .= '<strong>' . $title . '</strong>';
        }
        if ($message !== '') {
            $html .= '<p>' . $message . '</p>';
        }
        $html .= '</aside>';

        return $html;
    }

    protected static function renderTable(array $data): string {
        $content = $data['content'] ?? [];
        if (empty($content)) {
            return '';
        }

        $withHeadings = !empty($data['withHeadings']);

        $html = '<table>';
        if ($withHeadings) {
            $html .= '<thead><tr>';
            foreach ($content[0] as $cell) {
                $html .= '<th>' . static::sanitizeInline($cell) . '</th>';
            }
            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';
        $start = $withHeadings ? 1 : 0;
        for ($i = $start; $i < count($content); $i++) {
            $html .= '<tr>';
            foreach ($content[$i] as $cell) {
                $html .= '<td>' . static::sanitizeInline($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }
}
