<?php
class Pusherjs {
    public function push(){
        require 'vendor/autoload.php';
        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );
        $pusher = new Pusher\Pusher(
            '8cf91b33222fdfca79e1',
            '2008802539094dc08bbc',
            '1162342',
            $options
        );

        $data['testMessage'] = 'hello world';
        $pusher->trigger('my-channel', 'my-event', $data);
    }
    public function pushDebitnote(){
        require 'vendor/autoload.php';
        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );
        $pusher = new Pusher\Pusher(
            '29bc2e8a44dd757e9b8b',
            '43f8c62926b527fe3cbb',
            '1162342',
            $options
        );

        $data['testMessage'] = 'hello world';
        $pusher->trigger('my-channel', 'my-event', $data);
    }
}
