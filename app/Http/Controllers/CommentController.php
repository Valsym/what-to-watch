<?php

namespace App\Http\Controllers;


use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Providers\AuthServiceProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Responses\Success;
use App\Http\Responses\Fail;
use App\Services\CommentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;


class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->success([]);
    }

    /**
     * Добавление комментария
     *
     * @param StoreCommentRequest $request
     * @param                     $filmId
     *
     * @return SuccessResponse
     */
    public function store(StoreCommentRequest $request, $filmId): Success
    {
//        $commentService= new CommentService();
//        $comment = $this->commentService->createComment([
        $comment = Comment::create([
            'user_id' => Auth::user()->id,//auth()->id(),
            'film_id' => $filmId,
            'text' => $request->text,
            'rating' => $request->rating,
        ]);

        return $this->success($comment, 201);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return $this->success([]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();//User::find($id);
        $comment = Comment::find($id)->first();

        if (Gate::allows('comment-delete', $comment)
            && !$comment->replies()->exists()){ // и нет цепочки ком ментариев ниже
            // Юзер авторизован для выполнения этого действия

            if (Comment::destroy($id)) {
                return $this->success(null, 204);
            } else {
                return new Fail(
                    message: "Не удалось удалить комментарий user_id=$user->id coment_id=$id",
                    data: [
                        'id' => ['Неверный id.'],
                    ],
                    code: Response::HTTP_CONFLICT
                );
            }
        } else if ($comment->replies()->exists()) {
            throw new AuthorizationException('Нельзя удалить комментарий с ответами');
        } else {
            // Юзер не имеет доступа к удалению комментария
            abort(403, 'Комментарий может удалить только его автор или Модератор');
        }

        // Либо так (без Гейта)
//        $comment = Comment::where('id', $id)->first();
//        $deletedRows = DB::table('comments')->where('id', $id)->delete();
        if($user->isModerator() || $user->id === $comment->user_id) {

            if (Comment::destroy($id)) {
                return $this->success([]);
            } else {
                return new Fail(
                    message: "Не удалось удалить комментарий user_id=$user->id coment_id=$id",
                    data: [
                        'id' => ['Неверный id.'],
                    ],
                    code: Response::HTTP_CONFLICT
                );
            }
        }

        return new Fail(
            message: "Комментарий может удалить только его автор или Модератор",
            data: [],
            code: Response::HTTP_BAD_REQUEST
        );


    }
}
