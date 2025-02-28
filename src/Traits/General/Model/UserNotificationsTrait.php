<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\General\Model;

trait UserNotificationsTrait
{
    /* Todo:  need to sort out things */

    /*
    |--------------------------------------------------------------------------
    | Email Notification
    |--------------------------------------------------------------------------
    */

    /* ==================== User CRUD ==================== */
    //    public function notifyCreated($data = [])
    //    {
    //        $title = app_name() . " Notification";
    //        $body  = 'Dear Customer, you have been successfully registered with us.';
    //
    //        $notification = $this->notification($title, $body, TypeEnum::UserCreated, [], $this);
    //        $this->notifyMobile($title, $body);
    //        $this->notify(new RegisteredNotification(['un_hashed_password' => $data['password']]));
    //
    //        return $this;
    //    }


    /*
    |--------------------------------------------------------------------------
    | Mobile Notification
    |--------------------------------------------------------------------------
    */

//    public function notifyMobile($notification, array $data = [])
//    {
//        if (!$this->push_notifications) return;
//        $deviceToken = $this->getDeviceToken();
//        if (isset($deviceToken)) {
//            $result = send_fcm_notification($deviceToken, $notification, $data);
//            if ($result === true) {
//                $notification->update(['send_at' => now_now()]);
//            } elseif ($result === false) {
//                $notification->update(['exception' => 'Failed to send notification, device token/fcm project id not set']);
//            } else {
//                $notification->update(['exception' => $result]);
//            }
//        }
//    }


    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

//    public function notification($title, $body = '', $type = null, $data = [], $model = null)
//    {
//        $title    ??= app_name() . ' Notification';
//        $senderId = auth_check() ? auth_id() : null;
//        $status   = StatusEnum::UnRead->value;
//        return notification_create($this->id, $title, $body, $senderId, $model, $data, $type, $status);
//        return Notification::create([
//            'sender_id'       => $senderId,
//            'receiver_id'     => $this->id,
//            'notifiable_type' => isset($model) ? get_morphs_maps($model::class) : $model,
//            'notifiable_id'   => isset($model) ? $model->id : null,
//            'type'            => $type,
//            'title'           => $title,
//            'body'            => $body,
//            'data'            => $data,
//            'status'          => $status,
//        ]);
//    }

}
