<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\Message;
use App\Models\{PostDocument,PostDocumentComment};
use File;
use App\Http\Resources\DocumentCommentResource;




class DocumentController extends Controller
{
    public function realtime_document(Request $request){
        $validator = Validator::make($request->all(), [
            'id'        => 'required',
        ]);
        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }
        $message = [
            'id' => $request->id,
            'string' => $request->string,
            'annotation' => $request->annotation,
            'tag' => $request->tag,
        ];
        broadcast(new Message( json_decode( json_encode($message) ) ))->toOthers();
        return apiresponse(true, 'Document Sent',json_decode( json_encode($message) ));
    }

    public function updateDocument(Request $request){
        $validator = Validator::make($request->all(), [
            'id'        => 'required',
            'document'        => 'required',
        ]);
        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }
        $document = PostDocument::find($request->id);
        if($document){
            $previousFileName = public_path('images/'.$document->name);
            if(file_exists($previousFileName)){
                File::delete($previousFileName);   
            }
            $request->document->move(public_path('images'), $document->name);
        }
        return apiresponse(true, 'Document Updated',$document);
    }

    public function commentDocument(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'        => 'required',
            'document_id'    => 'required',
            'comment'        => 'required',
        ]);
        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }
        $PostDocumentComment = new PostDocumentComment();
        $PostDocumentComment->post_document_id = $request->document_id;
        $PostDocumentComment->user_id = $request->user_id;
        $PostDocumentComment->comment = $request->comment;
        if($PostDocumentComment->save()){
            $postDocumentComments = PostDocumentComment::where('id',$PostDocumentComment->id)->with('user')->first();
            //$postDocumentComments = DocumentCommentResource::collection($postDocumentComments)->response()->getData(true);
            return apiresponse(true, 'Comment uploaded',$postDocumentComments);
        }else{
            return apiresponse(false, 'Something went wrong');
        }
        
    }

    public function documentComments(Request $request){
        $validator = Validator::make($request->all(), [            
            'document_id'    => 'required',
        ]);
        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }
        $postDocumentComments = PostDocumentComment::where('post_document_id',$request->document_id)->with('user')->paginate(10);
        $postDocumentComments = DocumentCommentResource::collection($postDocumentComments)->response()->getData(true);
        $postDocumentComments['count'] = PostDocumentComment::where('post_document_id',$request->document_id)->count();
        return apiresponse(true, 'Comments found' ,$postDocumentComments);
    }

    public function deleteDocumentComment(Request $request){
        $validator = Validator::make($request->all(), [            
            'comment_id'    => 'required',
        ]);
        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }
        $postDocumentComment = PostDocumentComment::find($request->comment_id);
        if($postDocumentComment->delete()){
            return apiresponse(true, 'Comment deleted' );
        }else{
            return apiresponse(false, 'Something went wrong' );
        }
    }
}
