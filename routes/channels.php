<?php

use App\Models\User;
use App\Models\conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
// Broadcast::channel('private-events.{userId}', function ($user, $userId) {
//     return (int) $user->id === (int) $userId;
//     // return $user->id === User::findOrNew($userId)->user_id;
//   });
  Broadcast::channel('private-events-conversation.{chatID}', function ($user, $chatID) {
    // return (int) $user->id === (int) $chatID;
    return true;

  });

  Broadcast::channel('project.{conversation}', function ($user, $conversation) {
    // return (int) $user->id === (int) $chatID;
    return true;

  });
  Broadcast::channel('pprivate-ChatAboutOrder.{chatID}', function ($user, $chatID) {
    // return (int) $user->id === (int) $chatID;
    return true;

  });

  Broadcast::channel('private-conversation.{conversationID}', function ($user, $conversationID) {
    // return (int) $user->id === (int) $conversationID;
    return true;


  });
  Broadcast::channel('private-AcceptBidsEvent.{bidID}', function ($user, $bidID) {
    // return (int) $user->id === (int) $conversationID;
    return true;


  });
  Broadcast::channel('private-projects.{Projectstatus}', function ($user, $Projectstatus) {
    // return (int) $user->id === (int) $conversationID;
    return true;


  });
// Broadcast::channel('private-conversation.{conversationID}', function ($user, $conversationID) {
//     $conversation = conversation::find($conversationID);
//     // return   $conversation ;

//     // Check if the user is either the sender or the receiver of the conversation
//     return ($user->id === $conversation->user1_id || $user->id === $conversation->user2_id);
// });

  Broadcast::channel('private-eventForApproveOrRejectOrder.{sellerID}', function ($user, $sellerID) {
    return (int) $user->id === (int) 4;
    // return true;

  });
// Broadcast::channel('User', function ($user) {
//     return !is_null($user);
// });

