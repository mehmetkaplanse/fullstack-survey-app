<?php

namespace App\Notifications;

use App\Models\Survey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SurveyCreatedNotification extends Notification
{
    use Queueable;
    protected $survey;

    /**
     * Create a new notification instance.
     */
    public function __construct(Survey $survey)
    {
        $this->survey = $survey;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase(object $notifiable)
    {
        return [
            'title' => '📝 Yeni Anket Oluşturuldu.',
            'message' => "'{$this->survey->title}' başlıklı anketiniz başarıyla oluşturuldu. Katılımcılarla paylaşmaya başlayabilir veya anketi düzenlemeye devam edebilirsiniz.",
            'survey_id' => $this->survey->id,
            'time' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
