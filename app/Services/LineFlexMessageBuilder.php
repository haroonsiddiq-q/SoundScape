<?php

namespace App\Services;

class LineFlexMessageBuilder
{
    protected $altText = 'New notification';
    protected $headerText = 'Notification';
    protected $title = '';
    protected $bodyContents = [];
    protected $buttonLabel = 'View Details';
    protected $buttonUrl = '';
    protected $primaryColor = '#252c3c';

    public static function make()
    {
        return new static();
    }

    public function setAltText($text)
    {
        $this->altText = $text;
        return $this;
    }

    public function setHeader($headerText, $title)
    {
        $this->headerText = $headerText;
        $this->title = $title;
        return $this;
    }

    public function addChangeBox($field, $oldValue, $newValue)
    {
        $this->bodyContents[] = [
            'type' => 'box',
            'layout' => 'vertical',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => "• {$field}",
                    'weight' => 'bold',
                    'size' => 'sm',
                    'color' => $this->primaryColor
                ],
                [
                    'type' => 'text',
                    'text' => "Old: {$oldValue}",
                    'size' => 'xs',
                    'color' => '#888888',
                    'wrap' => true
                ],
                [
                    'type' => 'text',
                    'text' => "New: {$newValue}",
                    'size' => 'sm',
                    'weight' => 'bold',
                    'color' => '#333333',
                    'wrap' => true
                ]
            ]
        ];

        return $this;
    }

    public function addSimpleChangeBox($field, $message)
    {
        $this->bodyContents[] = [
            'type' => 'box',
            'layout' => 'vertical',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => "• {$field}",
                    'weight' => 'bold',
                    'size' => 'sm',
                    'color' => $this->primaryColor
                ],
                [
                    'type' => 'text',
                    'text' => $message,
                    'size' => 'sm',
                    'color' => '#888888',
                    'wrap' => true
                ]
            ]
        ];
        
        return $this;
    }

    public function setFooterButton($label, $url)
    {
        $this->buttonLabel = $label;
        $this->buttonUrl = $url;
        return $this;
    }

    public function toArray()
    {
        return [
            'type' => 'flex',
            'altText' => $this->altText,
            'contents' => [
                'type' => 'bubble',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $this->primaryColor,
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => $this->headerText,
                            'weight' => 'bold',
                            'color' => '#ffffff',
                            'size' => 'sm'
                        ],
                        [
                            'type' => 'text',
                            'text' => $this->title,
                            'weight' => 'bold',
                            'size' => 'xl',
                            'color' => '#ffffff',
                            'wrap' => true,
                            'margin' => 'sm'
                        ]
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'lg',
                    'contents' => empty($this->bodyContents)
                        ? [['type' => 'text', 'text' => 'No additional details']]
                        : $this->bodyContents
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => [
                                'type' => 'uri',
                                'label' => $this->buttonLabel,
                                'uri' => $this->buttonUrl
                            ],
                            'style' => 'primary',
                            'color' => $this->primaryColor
                        ]
                    ]
                ]
            ]
        ];
    }
}
