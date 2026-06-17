<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\LineChannel;
use App\Services\LineFlexMessageBuilder;

class ConcertUpdatedNotification extends Notification
{
    use Queueable;

    public $concert;
    public $changes;

    protected $hiddenFields = ['description', 'location', 'ticket_link'];

    public function __construct($concert, array $changes)
    {
        $this->concert = $concert;
        $this->changes = $this->groupLocationFields($changes);
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        $preferences = $notifiable->notification_preferences ?? [];

        if (in_array('email', $preferences)) {
            $channels[] = 'mail';
        }

        if (in_array('line', $preferences) && !empty($notifiable->line_id)) {
            $channels[] = LineChannel::class;
        }

        return $channels;
    }

    protected function groupLocationFields(array $changes)
    {
        $locationFields = ['venue_name', 'province_id', 'latitude', 'longitude'];
        $oldLocationParts = [];
        $newLocationParts = [];
        $hasLocationChange = false;

        foreach ($locationFields as $field) {
            if (array_key_exists($field, $changes)) {
                $hasLocationChange = true;
                $thaiFieldName = $this->translateField($field);

                $oldVal = $changes[$field]['old'] ?: '-';
                $newVal = $changes[$field]['new'] ?: '-';

                $oldLocationParts[] = "{$thaiFieldName}: {$oldVal}";
                $newLocationParts[] = "{$thaiFieldName}: {$newVal}";

                unset($changes[$field]);
            }
        }

        if ($hasLocationChange) {
            $changes['location'] = [
                'old' => implode(', ', $oldLocationParts),
                'new' => implode(', ', $newLocationParts),
            ];
        }

        return $changes;
    }

    protected function getOriginalConcertName()
    {
        if (isset($this->changes['name'])) {
            return $this->changes['name']['old'];
        }
        return $this->concert->name;
    }

    public function toMail($notifiable)
    {
        $url = route('concert.detail', $this->concert->id);
        $oldName = $this->getOriginalConcertName();

        $formattedChanges = [];
        foreach ($this->changes as $field => $change) {
            $formattedChanges[$this->translateField($field)] = $change;
        }

        $translatedHiddenFields = array_map(function ($field) {
            return $this->translateField($field);
        }, $this->hiddenFields);

        return (new MailMessage)
            ->subject("Concert \"{$oldName}\" has been updated")
            ->view('emails.concert-updated', [
                'userName' => $notifiable->name,
                'concertName' => $oldName,
                'changes' => $formattedChanges,
                'url' => $url,
                'hiddenFields' => $translatedHiddenFields,
            ]);
    }

    public function toLine($notifiable)
    {
        $oldName = $this->getOriginalConcertName();
        $url = route('concert.detail', $this->concert->id);
        
        $message = LineFlexMessageBuilder::make()
            ->setAltText("Concert \"{$oldName}\" has been updated")
            ->setHeader('Concert Information Updated', $oldName)
            ->setFooterButton('View Details on Website', $url);

        foreach ($this->changes as $field => $change) {
            $thaiField = $this->translateField($field);
            
            if (in_array($field, $this->hiddenFields)) {
                $message->addSimpleChangeBox($thaiField, 'Information has been updated');
            } else {
                $message->addChangeBox($thaiField, $change['old'], $change['new']);
            }
        }

        return $message->toArray();
    }

    public function toArray($notifiable)
    {
        $fieldsUpdated = count($this->changes);
        $summary = "There are {$fieldsUpdated} updates to the concert information";
        $oldName = $this->getOriginalConcertName();

        return [
            'concert_id' => $this->concert->id,
            'concert_name' => $oldName,
            'message' => $summary,
            'changes' => $this->changes,
        ];
    }

    private function translateField($field)
    {
        $map = [
            'name' => 'ชื่อConcert',
            'description' => 'รายละเอียด',
            'event_type' => 'ประเภทงาน',
            'genre' => 'แนวเพลง',
            'venue_name' => 'สถานที่จัดงาน',
            'province_id' => 'จังหวัด',
            'latitude' => 'ละติจูด',
            'longitude' => 'ลองจิจูด',
            'location' => 'ข้อมูลสถานที่ตั้ง',
            'price_min' => 'ราคาต่ำสุด',
            'price_max' => 'ราคาสูงสุด',
            'start_sale_date' => 'วันที่เริ่มขายบัตร',
            'end_sale_date' => 'วันสิ้นสุดการขายบัตร',
            'start_show_date' => 'วันที่เริ่มแสดง',
            'start_show_time' => 'เวลาเริ่มแสดง',
            'end_show_date' => 'วันที่สิ้นสุดการแสดง',
            'end_show_time' => 'เวลาสิ้นสุดการแสดง',
            'start_datetime' => 'วันและเวลาที่เริ่มแสดง',
            'ticket_link' => 'Linkซื้อบัตร',
            'Poster Picture' => 'Pictureโปสเตอร์',
            'Artists' => 'รายArtist Name',
        ];

        return $map[$field] ?? ucwords(str_replace('_', ' ', $field));
    }
}
