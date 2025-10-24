<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Comments\StoreCommentRequest;
use App\Http\Requests\Comments\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Http\Responses\Fail;
use App\Http\Responses\Success;
use App\Models\Comment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Success
     */
    public function index(int $filmId)
    {
//        $comments = Comment::where('film_id', $filmId)->get();
        $comments = Comment::with('user') // ← важно!
        ->where('film_id', $filmId)
            ->get();

//        return $this->success($comments, 201);
        return $this->success(CommentResource::collection($comments));
    }

    /**
     * Добавление комментария
     *
     * @param StoreCommentRequest $request
     * @param                     $filmId
     *
     * @return Success
     */
    public function store(StoreCommentRequest $request, $filmId): Success
    {
        $comment = Comment::create([
            'film_id' => $filmId,
            'user_id' => auth()->id(),
            'text' => $request->text,
            'rating' => $request->rating,
        ]);

        // Загружаем отношения
        $comment->load('user');

        return $this->success(new CommentResource($comment), 201);
//        return $this->success($comment, 201);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Success
     */
    public function update(UpdateCommentRequest $request, $id): Success
    {
        $user = Auth::user();
        $comment = Comment::find($id)->first();

        if ($user->isModerator() || (int)$user->id === $comment->user_id){
        //Gate::allows('update-comment', $comment)){
            $comment->update([
                'id' => $id,
                'text' => $request->text,
                'rating' => $request->rating,
            ]);

//            return $this->success($comment, 201);
            return $this->success(new CommentResource($comment), 200);
        }

        // Обычный юзер не имеет доступа к редактированию комментария
        abort(403, 'Комментарий может редактировать только его автор или Модератор');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Success
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $comment = Comment::find($id)->first();

        if (Gate::allows('comment-delete', $comment)){
            // Юзер авторизован для выполнения этого действия
            if ($comment->replies()->exists()) {
                if($user->isModerator()) {
                    $comment->replies()->delete();
                    $comment->delete();

                    return $this->success([], 204);
                }
                throw new AuthorizationException('Нельзя удалить комментарий с ответами');

            }

            $comment->delete();

            return $this->success([], 204);

        } else if ($user->id === $comment->user_id && $comment->replies()->exists()) {
            abort(403, 'Нельзя удалить комментарий с ответами');
        }
        // Юзер не имеет доступа к удалению комментария
        abort(403, 'Комментарий может удалить только его автор или Модератор');


        // Либо так (без Гейта)
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
