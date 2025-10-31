<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Comments\StoreCommentRequest;
use App\Http\Requests\Comments\UpdateCommentRequest;
use App\DTO\Comments\StoreCommentDto;
use App\DTO\Comments\UpdateCommentDto;
use App\Http\Resources\CommentResource;
use App\Http\Responses\Success;
use App\Services\Comments\CommentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(private CommentService $commentService) {}

    /**
     * @param int $filmId
     * @return Success
     */
    public function index(int $filmId): Success
    {
        $comments = $this->commentService->getFilmComments($filmId);

//        return $this->success($comments);
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
    public function store(StoreCommentRequest $request, int $filmId): Success
    {
        $storeCommentDto = new StoreCommentDto(
            text: $request->input('text'),
            rating: $request->input('rating'),
            film_id: $filmId,
            user_id: auth()->id(),
            parent_id: $request->input('comment_id')
        );

        $comment = $this->commentService->createComment($storeCommentDto);

//        return $this->success($comment->toArray(), 201);
        return $this->success(new CommentResource($comment), 201);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCommentRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateCommentRequest $request, int $id): Success
    {
        try {
            $updateCommentDto = new UpdateCommentDto(
                text: $request->input('text'),
                rating: $request->input('rating')
            );

            $user = auth()->user();
            $comment = $this->commentService->updateComment(
                $id,
                $updateCommentDto,
                $user->id,
                $user->isModerator()
            );

//            return $this->success($comment->toArray());
            return $this->success(new CommentResource($comment), 200);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): Success
    {
        try {
            $user = auth()->user();
            $this->commentService->deleteComment($id, $user->id, $user->isModerator());

            return $this->success(null, 204);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }
    }

}
