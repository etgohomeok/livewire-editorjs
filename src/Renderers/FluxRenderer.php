<?php

namespace EthanJenkins\LivewireEditorjs\Renderers;

use Illuminate\Support\Facades\Blade;

class FluxRenderer extends BaseRenderer {
    protected static function finalize(string $html): string {
        return Blade::render($html);
    }

    protected static function renderParagraph(array $data): string {
        $text = static::sanitizeInline($data['text'] ?? '');

        return '<flux:text>' . $text . '</flux:text>';
    }

    protected static function renderHeader(array $data): string {
        $text = static::sanitizeInline($data['text'] ?? '');
        $level = max(1, min(4, (int) ($data['level'] ?? 2)));

        $size = match ($level) {
            1 => 'xl',
            2 => 'lg',
            default => 'base',
        };

        $sizeAttr = $size !== 'base' ? " size=\"{$size}\"" : '';

        return "<flux:heading level=\"{$level}\"{$sizeAttr}>{$text}</flux:heading>";
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
            $html .= '<figcaption><flux:text class="mt-1 text-center text-sm">' . static::sanitizeInline($caption) . '</flux:text></figcaption>';
        }
        $html .= '</figure>';

        return $html;
    }

    protected static function renderDelimiter(): string {
        return '<flux:separator class="my-6" />';
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

            $html .= '<li><flux:text inline>' . $content . '</flux:text>';

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
        $html = '<flux:checkbox.group>';
        foreach ($items as $item) {
            $content = e(strip_tags($item['content'] ?? ''));
            $checked = !empty($item['meta']['checked']);
            $checkedAttr = $checked ? ' checked' : '';
            $disabledAttr = ' disabled';

            $html .= '<flux:checkbox label="' . $content . '"' . $checkedAttr . $disabledAttr . ' />';

            if (!empty($item['items'])) {
                $html .= '<div class="pl-6">' . static::renderChecklistItems($item['items']) . '</div>';
            }
        }
        $html .= '</flux:checkbox.group>';
        return $html;
    }

    protected static function renderQuote(array $data): string {
        $text = static::sanitizeInline($data['text'] ?? '');
        $caption = $data['caption'] ?? '';
        $alignment = ($data['alignment'] ?? 'left') === 'center' ? 'center' : 'left';

        $alignClass = $alignment === 'center' ? 'text-center' : 'text-left';

        $html = '<blockquote class="border-l-4 border-gray-300 pl-4 py-2 italic ' . $alignClass . '">';
        $html .= '<flux:text>' . $text . '</flux:text>';

        if ($caption !== '') {
            $html .= '<footer class="mt-2"><flux:text class="text-sm not-italic" variant="subtle">&mdash; ' . static::sanitizeInline($caption) . '</flux:text></footer>';
        }
        $html .= '</blockquote>';
        return $html;
    }

    protected static function renderWarning(array $data): string {
        $title = e($data['title'] ?? '');
        $message = e($data['message'] ?? '');

        $html = '<flux:callout color="yellow" icon="exclamation-triangle">';
        if ($title !== '') {
            $html .= '<flux:callout.heading>' . $title . '</flux:callout.heading>';
        }
        if ($message !== '') {
            $html .= '<flux:callout.text>' . $message . '</flux:callout.text>';
        }
        $html .= '</flux:callout>';
        return $html;
    }

    protected static function renderTable(array $data): string {
        $withHeadings = !empty($data['withHeadings']);
        $content = $data['content'] ?? [];

        if (empty($content)) {
            return '';
        }

        $html = '<flux:table>';

        if ($withHeadings && !empty($content[0])) {
            $html .= '<flux:table.columns>';
            foreach ($content[0] as $cell) {
                $html .= '<flux:table.column>' . static::sanitizeInline($cell) . '</flux:table.column>';
            }
            $html .= '</flux:table.columns>';
        }

        $html .= '<flux:table.rows>';
        $startIndex = $withHeadings ? 1 : 0;
        for ($i = $startIndex; $i < count($content); $i++) {
            $html .= '<flux:table.row>';
            foreach ($content[$i] as $cell) {
                $html .= '<flux:table.cell>' . static::sanitizeInline($cell) . '</flux:table.cell>';
            }
            $html .= '</flux:table.row>';
        }
        $html .= '</flux:table.rows>';
        $html .= '</flux:table>';

        return $html;
    }
}
