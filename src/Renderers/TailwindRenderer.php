<?php

namespace EthanJenkins\LivewireEditorjs\Renderers;

class TailwindRenderer extends BaseRenderer {
    protected static function renderParagraph(array $data): string {
        $text = static::sanitizeInline($data['text'] ?? '');

        return '<p class="text-base">' . $text . '</p>';
    }

    protected static function renderHeader(array $data): string {
        $text = static::sanitizeInline($data['text'] ?? '');
        $level = max(1, min(6, (int) ($data['level'] ?? 2)));

        $sizeClass = match ($level) {
            1 => 'text-3xl font-bold',
            2 => 'text-2xl font-semibold',
            3 => 'text-xl font-semibold',
            4 => 'text-lg font-semibold',
            5 => 'text-base font-semibold',
            default => 'text-sm font-semibold',
        };

        return "<h{$level} class=\"{$sizeClass}\">{$text}</h{$level}>";
    }

    protected static function renderImage(array $data): string {
        $url = e($data['file']['url'] ?? '');
        $caption = $data['caption'] ?? '';

        if ($url === '') {
            return '';
        }

        $classes = [];
        if (!empty($data['withBorder'])) $classes[] = 'border border-gray-200 rounded';
        if (!empty($data['withBackground'])) $classes[] = 'bg-gray-100 p-4';
        if (!empty($data['stretched'])) $classes[] = 'w-full';

        $classAttr = $classes ? ' class="' . implode(' ', $classes) . '"' : '';

        $alt = e(strip_tags($caption));

        $html = "<figure{$classAttr}>";
        $html .= "<img src=\"{$url}\" alt=\"{$alt}\">";
        if ($caption !== '') {
            $html .= '<figcaption class="mt-1 text-center text-sm">' . static::sanitizeInline($caption) . '</figcaption>';
        }
        $html .= '</figure>';

        return $html;
    }

    protected static function renderDelimiter(): string {
        return '<hr class="my-6 border-gray-200">';
    }

    protected static function renderList(array $data): string {
        $style = $data['style'] ?? 'unordered';
        $items = $data['items'] ?? [];

        if ($style === 'checklist') {
            return static::renderChecklistItems($items);
        }

        $tag = $style === 'ordered' ? 'ol' : 'ul';
        $classes = $style === 'ordered' ? 'list-decimal pl-6' : 'list-disc pl-6';

        return "<{$tag} class=\"{$classes}\">" . static::renderListItems($items, $style) . "</{$tag}>";
    }

    protected static function renderListItems(array $items, string $style): string {
        $html = '';
        foreach ($items as $item) {
            $content = static::sanitizeInline($item['content'] ?? '');
            $children = $item['items'] ?? [];

            $html .= '<li><span>' . $content . '</span>';

            if (!empty($children)) {
                $tag = $style === 'ordered' ? 'ol' : 'ul';
                $classes = $style === 'ordered' ? 'list-decimal pl-6' : 'list-disc pl-6';
                $html .= "<{$tag} class=\"{$classes}\">" . static::renderListItems($children, $style) . "</{$tag}>";
            }

            $html .= '</li>';
        }
        return $html;
    }

    protected static function renderChecklistItems(array $items): string {
        $html = '<div class="flex flex-col gap-2">';
        foreach ($items as $item) {
            $content = static::sanitizeInline($item['content'] ?? '');
            $checked = !empty($item['meta']['checked']) ? ' checked' : '';

            $html .= '<label class="flex items-center gap-2">';
            $html .= '<input type="checkbox" disabled' . $checked . '>';
            $html .= '<span>' . $content . '</span>';
            $html .= '</label>';

            if (!empty($item['items'])) {
                $html .= '<div class="pl-6">' . static::renderChecklistItems($item['items']) . '</div>';
            }
        }
        $html .= '</div>';

        return $html;
    }

    protected static function renderQuote(array $data): string {
        $text = static::sanitizeInline($data['text'] ?? '');
        $caption = $data['caption'] ?? '';
        $alignment = ($data['alignment'] ?? 'left') === 'center' ? 'center' : 'left';

        $alignClass = $alignment === 'center' ? 'text-center' : 'text-left';

        $html = '<blockquote class="border-l-4 border-gray-300 pl-4 py-2 italic ' . $alignClass . '">';
        $html .= '<p class="text-base">' . $text . '</p>';

        if ($caption !== '') {
            $html .= '<footer class="mt-2"><span class="text-sm not-italic text-gray-500">&mdash; ' . static::sanitizeInline($caption) . '</span></footer>';
        }
        $html .= '</blockquote>';

        return $html;
    }

    protected static function renderWarning(array $data): string {
        $title = e($data['title'] ?? '');
        $message = e($data['message'] ?? '');

        $html = '<aside class="border-l-4 border-yellow-400 bg-yellow-50 p-4">';
        if ($title !== '') {
            $html .= '<strong class="block font-semibold">' . $title . '</strong>';
        }
        if ($message !== '') {
            $html .= '<p class="mt-1">' . $message . '</p>';
        }
        $html .= '</aside>';

        return $html;
    }

    protected static function renderTable(array $data): string {
        $withHeadings = !empty($data['withHeadings']);
        $content = $data['content'] ?? [];

        if (empty($content)) {
            return '';
        }

        $html = '<table class="w-full border-collapse">';

        if ($withHeadings && !empty($content[0])) {
            $html .= '<thead><tr>';
            foreach ($content[0] as $cell) {
                $html .= '<th class="border p-2 text-left">' . static::sanitizeInline($cell) . '</th>';
            }
            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';
        $startIndex = $withHeadings ? 1 : 0;
        for ($i = $startIndex; $i < count($content); $i++) {
            $html .= '<tr>';
            foreach ($content[$i] as $cell) {
                $html .= '<td class="border p-2">' . static::sanitizeInline($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }
}
