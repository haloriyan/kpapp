<?php

namespace App\Http\Controllers\Api;

use Str;
use App\Http\Controllers\Controller;
use App\Models\Enroll;
use App\Models\Thread;
use App\Models\ThreadReply;
use App\Models\ThreadReplyVote as ReplyVote;
use App\Models\ThreadVote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForumController extends Controller
{
    public function getThread($id, Request $request) {
        $user = User::where('token', $request->token)->first();
        $thread = Thread::where('id', $id)->with(['user'])->first();
        $thread->i_have_upvoted = false;
        $thread->i_have_downvoted = false;

        if ($user != null) {
            $baseFilter = [
                ['user_id', $user->id],
                ['thread_id', $thread->id]
            ];
            $thread->i_have_upvoted = ThreadVote::where([...$baseFilter, ['type', 'upvote']])->get(['id'])->count() > 0;
            $thread->i_have_downvoted = ThreadVote::where([...$baseFilter, ['type', 'downvote']])->get(['id'])->count() > 0;
        }

        return response()->json([
            'thread' => $thread,
        ]);
    }
    public function postThread(Request $request) {
        $user = User::where('token', $request->token)->first();
        $saveData = Thread::create([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
            'title' => $request->title,
            'body' => $request->body,
            'upvote_count' => 0,
            'downvote_count' => 0,
            'comments_count' => 0,
        ]);

        return response()->json([
            'message' => "ok"
        ]);
    }
    public function voteThread($id, $type, Request $request) {
        $user = User::where('token', $request->token)->first();
        $data = Thread::where('id', $id);
        $thread = $data->first();
        $oppositeType = $type === "upvote" ? "downvote" : "upvote";

        $baseFilter = [
            ['thread_id', $id],
            ['user_id', $user->id],
        ];
        $voteFilter = [...$baseFilter, ['type', $type]];
        $oppositeFilter = [...$baseFilter, ['type', $oppositeType]];
        
        $v = ThreadVote::where($voteFilter);
        $vote = $v->get(['id']);
        $deleteOpposite = ThreadVote::where($oppositeFilter)->delete();
        
        $colName = $type . "_count";
        $oppositeColName = $oppositeType . '_count';
        if ($thread->{$oppositeColName} > 0) {
            $data->decrement($oppositeColName);
        }

        if ($vote->count() == 0) {
            $createVote = ThreadVote::create([
                'user_id' => $user->id,
                'thread_id' => $id,
                'type' => $type,
            ]);
            $data->increment($colName);
        } else {
            $v->delete();
            if ($thread->{$colName} > 0) {
                $data->decrement($colName);
            }
        }

        return response()->json([
            'message' => "ok"
        ]);
    }

    public function getReply($id, Request $request) {
        $user = User::where('token', $request->token)->first();
        $ableToReply = false;

        $thread = Thread::where('id', $id)->first();

        $replies = ThreadReply::where('thread_id', $id)
        ->with(['user'])
        ->orderBy('upvote_count', 'DESC')->orderBy('downvote_count', 'ASC')->orderBy('created_at', 'DESC')
        ->get();

        if ($user != null) {
            $enroll = Enroll::where([
                ['user_id', $user->id],
                ['course_id', $thread->course_id]
            ])->get(['id']);

            if ($enroll->count() > 0) {
                $ableToReply = true;
            }

            foreach ($replies as $t => $reply) {
                $replies[$t]->i_have_upvoted = false;
                $replies[$t]->i_have_downvoted = false;
    
                $baseFilter = [
                    ['user_id', $user->id],
                    ['reply_id', $reply->id]
                ];

                $replies[$t]->i_have_upvoted = ReplyVote::where([...$baseFilter, ['type', 'upvote']])->get(['id'])->count() > 0;
                $replies[$t]->i_have_downvoted = ReplyVote::where([...$baseFilter, ['type', 'downvote']])->get(['id'])->count() > 0;
            }
        }

        return response()->json([
            'replies' => $replies,
            'able_to_reply' => $ableToReply,
        ]);
    }
    public function postReply($id, Request $request) {
        $user = User::where('token', $request->token)->first();

        $saveData = ThreadReply::create([
            'user_id' => $user->id,
            'thread_id' => $id,
            'body' => $request->body,
            'upvote_count' => 0,
            'downvote_count' => 0,
        ]);

        $updateCounter = Thread::where('id', $id)->increment('comments_count');

        return response()->json([
            'message' => "ok"
        ]);
    }
    
    public function voteReply($id, $replyID, $type, Request $request) {
        $user = User::where('token', $request->token)->first();
        $data = ThreadReply::where('id', $replyID);
        $reply = $data->first();
        $oppositeType = $type === "upvote" ? "downvote" : "upvote";

        $baseFilter = [
            ['reply_id', intval($replyID)],
            ['user_id', $user->id],
        ];
        $voteFilter = [...$baseFilter, ['type', $type]];
        $oppositeFilter = [...$baseFilter, ['type', $oppositeType]];
        
        $v = ReplyVote::where($voteFilter);
        $vote = $v->get(['id']);
        $deleteOpposite = ReplyVote::where($oppositeFilter)->delete();
        
        $colName = $type . "_count";
        $oppositeColName = $oppositeType . '_count';
        if ($reply->{$oppositeColName} > 0) {
            $data->decrement($oppositeColName);
        }

        if ($vote->count() == 0) {
            $createVote = ReplyVote::create([
                'user_id' => $user->id,
                'reply_id' => $reply->id,
                'type' => $type,
            ]);
            $data->increment($colName);
        } else {
            $v->delete();
            if ($reply->{$colName} > 0) {
                $data->decrement($colName);
            }
        }

        return response()->json([
            'message' => "ok"
        ]);
    }
}
