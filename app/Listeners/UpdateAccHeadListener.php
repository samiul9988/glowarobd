<?php

namespace App\Listeners;

use Log;
use Throwable;
use App\Models\AccHead;
use App\Models\AccTransaction;
use App\Events\UpdateAccHead;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateAccHeadListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\UpdateAccHead  $event
     * @return void
     */
    public function handle(UpdateAccHead $event)
    {
        DB::beginTransaction();
        try{
            $theHead = AccHead::find($event->id);
            $data = (object) $event->data;

            if(!empty($theHead)){
                $originalHead = $theHead->head;

                $theHead->parent_head = $data->parent_head;
                $theHead->sub_head = $data->sub_head;
                $theHead->head = $data->head;

                $headChanged = $originalHead !== $data->head;

                if ($headChanged) {
                    AccTransaction::where('head', $originalHead)->update(['head' => $data->head]);
                    AccTransaction::where('head_id', $theHead->id)
                        ->where('head_type', AccHead::class)
                        ->update(['head' => $data->head]);
                }

                $theHead->reference_id = $data->reference_id;
                $theHead->reference_type = $data->reference_type;
                $theHead->user_id = $data->user_id;

                if($theHead->save()){
                    DB::commit();
                } else {
                    throw new \Exception('Failed to update account head for ID: ' . $event->id);
                }
            }else{
                throw new \Exception('Account head not found for ID: ' . $event->id);
            }
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UpdateAccHead $event, Throwable $exception): void
    {
        Log::error($exception);
    }
}
