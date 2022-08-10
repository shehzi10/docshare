<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Post,PostDocument,Taggedfriend,UserFriend,Notification,GroupSharedDocument,Chatlist,Message,GroupMessage};
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File; 
use App\Http\Resources\PostResource;
use App\Http\Resources\DocumentResource;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;




class PostController extends Controller
{
    public function index2(){ 
        $b[] = null;
        $friends = UserFriend::where('user_id', Auth::user()->id)->where('status','approved')->get();
        $a[] = Auth::user()->id;
        foreach($friends as $friend){
            $a[] = $friend->requested_user_id;
        }
        $taged_friends = Taggedfriend::whereIn('user_id', $a)->get();
        if($taged_friends->count() > 0){
            foreach($taged_friends as $fri){
                $b[] = $fri->user_id;
            }
            $queryParam = 'user_id';

        }else{
            $queryParam = 'user_id';
            $b = $a;
            
        }
       
        $posts = Post::whereIn($queryParam, array_unique($b))->with(['documents' => function($q){
            $q->orderBy('updated_at','desc');
        },'taggedFriends','user'])->paginate(10);

        $data = PostResource::collection($posts)->response()->getData(true);
        return apiresponse(true, 'Posts Found', $data);
    }

    public function index(){ 
        $total_posts[] = null;
        $posts = Post::where('user_id',Auth::user()->id)->get();
        foreach($posts as $post){
            $total_posts[] = $post->id;
        }
        $Taggedfriend = Taggedfriend::where('user_id',Auth::user()->id)->get();
        foreach($Taggedfriend as $tag){
            $total_posts[] = $tag->post_id;
        }
        $posts = Post::whereIn('id', array_unique($total_posts))->with(['documents' => function($q){
            $q->orderBy('updated_at','desc');
        },'taggedFriends','user'])->paginate(10);
        $data = PostResource::collection($posts)->response()->getData(true);
        return apiresponse(true, 'Posts Found', $data);
    }


    public function getAllDocuments(){
        $documents = PostDocument::where('user_id', Auth::user()->id)->where('type','<>','image')->orderBy('updated_at','desc')->paginate(5);
        $data  = DocumentResource::collection($documents)->response()->getData(true);
        return apiresponse(true, 'Documents Found', $data);
    }

    public function setDocumentPasscode(Request $request){
        $validator = Validator::make($request->all(), [
            'id'        => 'required',
            'passcode'  => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $document = PostDocument::find($request->id);
        if($document){
            $document->is_protected = 1;
            $document->key          = Hash::make($request->passcode);
            if($document->save()){
                return apiresponse('true','Document Encrypted');
            }else{
                return apiresponse('true','Something went wrong');
            }
        }else{
            return apiresponse('true','Document not found');
        }
    }


    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'description'   => 'required',
            'documents'     => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $post = new Post();
        $post->title        = $request->title;
        $post->description  = $request->description;
        $post->user_id      = Auth::user()->id;
        if($post->save()){
            foreach($request->documents as $doc){
                $document = new PostDocument();
                $document->type = $doc['type'];
                if(isset($doc['is_protected']) && $doc['is_protected'] == 1){
                    $document->is_protected = $doc['is_protected'];
                    $document->key = Hash::make($doc['key']);
                }
                $document->post_id = $post->id;
                $document->user_id = Auth::user()->id;
                $filename = time().'.'.$doc['document']->getClientOriginalExtension();
                $doc['document']->move(public_path('images'), $filename);
                $document->name = $filename;
                $document->save();
            }
            if($request->has('tagFriends')){
                foreach ($request->tagFriends as $value) {
                    $Taggedfriend = new Taggedfriend();
                    $Taggedfriend->post_id = $post->id;
                    $Taggedfriend->user_id = $value;
                    $Taggedfriend->save();
                    $title =  Auth::user()->username .' tagged you in '.$post->name.' post' ;
                    $body = Auth::user()->username .' tagged you in '.$post->name.' post' ;
                    SendNotification($Taggedfriend->user->device_id, $title, $body);
                    $notification = new Notification();
                    $notification->sender_id                =   Auth::user()->id;
                    $notification->reciever_id              =   $Taggedfriend->user_id;
                    $notification->title                    =   $title;
                    $notification->body                     =   $body;
                    $notification->type                     =   'post';
                    $notification->content_id               =   $post->id;
                    $notification->save();
                }
            }
            return apiresponse(true, 'Post uploaded');
        }
    }


    public function openDocument(Request $request){
        $validator = Validator::make($request->all(), [
            'document_id'     => 'required'
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $document = PostDocument::find($request->document_id);
        if($document != null && $document->is_protected == 1){
            if(Hash::check($request->key, $document->key)){
                $document->updated_at = Carbon::now();
                if($document->save()){
                    return apiresponse(true, 'Key matched',$document);
                }
            }else{
                return apiresponse(false, 'Incorrect key');
            }
        }else{
            if($document != null){
                $document->updated_at = Carbon::now();
                if($document->save()){
                    return apiresponse(true, 'Document updated',$document);
                }
            }else{
                return apiresponse(true, 'Document not found');
            }
        }
    }


    public function deleteDocument(Request $request){
        $validator = Validator::make($request->all(), [
            'document_id'     => 'required'
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $document = PostDocument::findorfail($request->document_id);
        if($document){
            $filename = public_path('images/'.$document->name);
            File::delete($filename);
            $document->delete();
            return apiresponse(true, 'Document deleted');
        }
    }


    public function destroy(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id'     => 'required'
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $post = Post::where('id', $request->post_id)->with('documents')->first();
        if($post){
            foreach($post->documents as $doc){
                // $filename = public_path('images/'.$doc->name);
                // File::delete($filename);
                PostDocument::findorfail($doc->id)->delete();
            }
            if($post->delete()){
                return apiresponse(true, 'Post deleted');
            }else{
                return apiresponse(false, 'Something Went Wrong');
            }
        }
    }



    public function removeTag(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id'     => 'required',
            'user_id'     => 'required'
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
            $Taggedfriend = Taggedfriend::where('post_id',$request->post_id)->where('user_id',$request->user_id)->delete();
            if($Taggedfriend){
                return apiresponse(true, 'Removed from tag');
            }else{
                return apiresponse(false,'Something went wrong');
            }
    }


    public function shareDocument(Request $request){
        $validator = Validator::make($request->all(), [
            'document_id'   => 'required',
            'type'          => 'required',
            'to'            => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        if($request->type == 'message'){
            $chatlist = Chatlist::find($request->to);
            if($chatlist){
                $messageData = [
                    'chatlist_id'       => $chatlist->id,
                    'type'              => 'shared_document',
                    'sent_from_type'    => $chatlist->from_user_type,
                    'sent_from_id'      => $chatlist->from_user_id,
                    'sent_to_type'      => $chatlist->to_user_type,
                    'sent_to_id'        => $chatlist->to_user_id,
                    'post_document_id'  => $request->document_id,
                ]; 
                $message = Message::create($messageData);
                if($message){
                    return apiresponse(true, 'Document shared');
                }else{
                    return apiresponse(false,'Something went wrong');
                }
            }
        }
        if($request->type == 'group'){
            $groupMessage = new GroupMessage();
            $groupMessage->type = 'shared_document';
            $groupMessage->group_id  = $request->to;
            $groupMessage->user_id   = Auth::user()->id;
            if($groupMessage->save()){
                $sharedDocument = new GroupSharedDocument();
                $sharedDocument->group_message_id  = $groupMessage->id;
                $sharedDocument->post_document_id  = $request->document_id;
                if($sharedDocument->save()){
                    return apiresponse(true, 'Document shared');
                }else{
                    return apiresponse(false, 'Something went wrong');
                }
            }
        }

    }
}
