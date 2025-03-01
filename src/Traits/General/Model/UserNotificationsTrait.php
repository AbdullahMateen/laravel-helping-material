<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model;

use AbdullahMateen\LaravelHelpingMaterial\Enums\Notification\StatusEnum;
use AbdullahMateen\LaravelHelpingMaterial\Models\Notification;

trait UserNotificationsTrait
{

    /*
    |--------------------------------------------------------------------------
    | Mobile Notification
    |--------------------------------------------------------------------------
    */

    public function notifyMobile($notification, array $data = [])
    {
        if (!$this->push_notifications) return;
        $deviceToken = $this->getDeviceToken();
        if (isset($deviceToken)) {
            $result = send_fcm_notification($deviceToken, $notification, $data);
            if ($result === true) {
                $notification->update(['send_at' => now_now()]);
            } elseif ($result === false) {
                $notification->update(['exception' => 'Failed to send notification, device token/fcm project id not set']);
            } else {
                $notification->update(['exception' => $result]);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

    public function notification($title, $body = '', $type = null, $data = [], $model = null)
    {
        $title    ??= app_name() . ' Notification';
        $senderId = auth_check() ? auth_id() : null;
        $status   = StatusEnum::Unread->value;
        return Notification::create([
            'sender_id'       => $senderId,
            'receiver_id'     => $this->id,
            'notifiable_type' => isset($model) ? get_morphs_maps($model::class) : $model,
            'notifiable_id'   => isset($model) ? $model->id : null,
            'type'            => $type,
            'title'           => $title,
            'body'            => $body,
            'data'            => $data,
            'status'          => $status,
        ]);
    }

}
