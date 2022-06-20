<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostDocument;
use App\Models\Taggedfriend;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File; 
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;



class PostController extends Controller
{
    public function index(){
        $posts = Post::where('user_id', Auth::user()->id)->with(['documents' => function($q){
            $q->orderBy('updated_at','desc');
        },'taggedFriends'])->simplePaginate(5);
        $data = PostResource::collection($posts)->response()->getData(true);
        // $data['posts'] = $posts;
        return apiresponse(true, 'Posts Found', $data);
    }


    public function getAllDocuments(){
        $documents = PostDocument::where('user_id', Auth::user()->id)->orderBy('updated_at','desc')->simplePaginate(5);
        $data['documents'] = $documents;
        return apiresponse(true, 'Documents Found', $data);
    }


    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'title'         => 'required',
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
        $document = PostDocument::findorfail($request->document_id);
        if($document->is_protected == 1){
            if(Hash::check($request->key, $document->key)){
                $document->updated_at = Carbon::now();
                if($document->save()){
                    return apiresponse(true, 'Key matched',$document);
                }
            }else{
                return apiresponse(false, 'Incorrect key');
            }
        }else{
            $document->updated_at = Carbon::now();
            if($document->save()){
                return apiresponse(true, 'Document updated',$document);
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
                $filename = public_path('images/'.$doc->name);
                File::delete($filename);
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
}
