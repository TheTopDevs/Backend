<?php


namespace App\Http\Controllers\Api\Notification;


use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\ArrayNotificationRequest;
use App\Http\Requests\Notification\ArrayWithConditionNotificationRequest;
use App\Http\Requests\Notification\ListNotificationRequest;
use App\Http\Resources\Notification\NotificationResource;
use App\Models\Notification;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * @param ListNotificationRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function all(ListNotificationRequest $request)
    {
        $query = Notification::query();
        $query = $this->notificationService->addUserConditionToQuery($query, Auth::id());

        if ($request->sort == ListNotificationRequest::SYSTEM) {
            $query = $this->notificationService->addTypeToQuery($query, Notification::SYSTEM_NOTIFICATION_TYPE);
        }
        if ($request->sort == ListNotificationRequest::TRADE) {
            $query = $this->notificationService->addTypeToQuery($query, Notification::TRADE_NOTIFICATION_TYPE);
        }
        if ($request->sort == ListNotificationRequest::FIXED) {
            $query = $this->notificationService->addFixedStatusToQuery($query);
        }

        $query->orderByDesc('created_at');
        return NotificationResource::collection($query->paginate());
    }

    /**
     * @param ArrayNotificationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeNotifications(ArrayNotificationRequest $request)
    {
        $query = $this->notificationService->addUserConditionToQuery(Notification::query(), Auth::id());
        $query = $this->notificationService->addIdArrayConditionToQuery($query, $request->data);
        return response()->json((bool)($query->delete()));
    }

    public function markNotificationsAsRead(ArrayWithConditionNotificationRequest $request)
    {
        $query = $this->notificationService->addUserConditionToQuery(Notification::query(), Auth::id());
        $query = $this->notificationService->addIdArrayConditionToQuery($query, $request->data);
        $query->update(['is_read' => $request->isEnable]);

        return response()->json();
    }

    public function markNotificationsAsFixed(ArrayWithConditionNotificationRequest $request)
    {
        $query = $this->notificationService->addUserConditionToQuery(Notification::query(), Auth::id());
        $query = $this->notificationService->addIdArrayConditionToQuery($query, $request->data);
        $query->update(['is_fixed' => $request->isEnable]);

        return response()->json();
    }
}