<?php

namespace App\Http\Controllers;

class DocumentationController extends Controller
{
    public function index()
    {
        $components = [
            [
                'name' => 'Button',
                'path' => 'button',
                'category' => 'Common',
                'description' => 'Reusable button component with multiple variants'
            ],
            [
                'name' => 'Input',
                'path' => 'input',
                'category' => 'Common',
                'description' => 'Text input field with validation support'
            ],
            [
                'name' => 'Select',
                'path' => 'select',
                'category' => 'Common',
                'description' => 'Select dropdown for form selections'
            ],
            [
                'name' => 'Textarea',
                'path' => 'textarea',
                'category' => 'Common',
                'description' => 'Multi-line text input component'
            ],
            [
                'name' => 'Checkbox',
                'path' => 'checkbox',
                'category' => 'Common',
                'description' => 'Checkbox input with label support'
            ],
            [
                'name' => 'Card',
                'path' => 'card',
                'category' => 'Layout',
                'description' => 'Container component for content grouping'
            ],
            [
                'name' => 'Modal',
                'path' => 'modal',
                'category' => 'Layout',
                'description' => 'Dialog/modal component for overlays'
            ],
            [
                'name' => 'Tabs',
                'path' => 'tabs',
                'category' => 'Layout',
                'description' => 'Tabbed interface component'
            ],
            [
                'name' => 'Accordion',
                'path' => 'accordion',
                'category' => 'Layout',
                'description' => 'Collapsible accordion component'
            ],
            [
                'name' => 'Breadcrumb',
                'path' => 'breadcrumb',
                'category' => 'Layout',
                'description' => 'Navigation breadcrumb component'
            ],
            [
                'name' => 'Divider',
                'path' => 'divider',
                'category' => 'Layout',
                'description' => 'Visual separator component'
            ],
            [
                'name' => 'Alert',
                'path' => 'alert',
                'category' => 'Feedback',
                'description' => 'Alert/notification component'
            ],
            [
                'name' => 'Badge',
                'path' => 'badge',
                'category' => 'Feedback',
                'description' => 'Badge/label component for status'
            ],
            [
                'name' => 'Progress',
                'path' => 'progress',
                'category' => 'Feedback',
                'description' => 'Progress bar component'
            ],
            [
                'name' => 'Spinner',
                'path' => 'spinner',
                'category' => 'Feedback',
                'description' => 'Loading spinner component'
            ],
            [
                'name' => 'Avatar',
                'path' => 'avatar',
                'category' => 'Bonus',
                'description' => 'User avatar component with status indicators'
            ],
            [
                'name' => 'Chip',
                'path' => 'chip',
                'category' => 'Bonus',
                'description' => 'Chip/tag component for selections and filters'
            ],
            [
                'name' => 'Dropdown',
                'path' => 'dropdown',
                'category' => 'Bonus',
                'description' => 'Dropdown menu component for actions'
            ],
        ];

        return view('documentation.index', ['components' => $components]);
    }

    public function component($component)
    {
        $components = [
            'button', 'input', 'select', 'textarea', 'checkbox',
            'card', 'modal', 'tabs', 'accordion', 'breadcrumb', 'divider',
            'alert', 'badge', 'progress', 'spinner',
            'avatar', 'chip', 'dropdown',
        ];

        if (!in_array($component, $components)) {
            abort(404);
        }

        return view("documentation.components.{$component}");
    }
}
