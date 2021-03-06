<?php

namespace App\Http\Controllers\Api\Account\Activity;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Api\ApiController;
use App\Models\Account\ActivityTypeCategory;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Account\Activity\ActivityTypeCategory\CreateActivityTypeCategory;
use App\Services\Account\Activity\ActivityTypeCategory\UpdateActivityTypeCategory;
use App\Services\Account\Activity\ActivityTypeCategory\DestroyActivityTypeCategory;
use App\Http\Resources\Activity\ActivityTypeCategory as ActivityTypeCategoryResource;

class ApiActivityTypeCategoryController extends ApiController
{
    /**
     * Get the list of activity type categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $activityTypeCategories = auth()->user()->account->activityTypeCategories()
                ->orderBy($this->sort, $this->sortDirection)
                ->paginate($this->getLimitPerPage());
        } catch (QueryException $e) {
            return $this->respondInvalidQuery();
        }

        return ActivityTypeCategoryResource::collection($activityTypeCategories);
    }

    /**
     * Get the detail of a given activity type category.
     *
     * @param  Request $request
     * @param  int $activityTypeCategoryId
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $activityTypeCategoryId)
    {
        try {
            $activityTypeCategory = ActivityTypeCategory::where('account_id', auth()->user()->account_id)
                ->where('id', $activityTypeCategoryId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        }

        return new ActivityTypeCategoryResource($activityTypeCategory);
    }

    /**
     * Store the activity type category.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $activityTypeCategory = app(CreateActivityTypeCategory::class)->execute(
                $request->all()
                    +
                    [
                    'account_id' => auth()->user()->account->id,
                ]
            );
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        } catch (ValidationException $e) {
            return $this->respondValidatorFailed($e->validator);
        } catch (QueryException $e) {
            return $this->respondInvalidQuery();
        }

        return new ActivityTypeCategoryResource($activityTypeCategory);
    }

    /**
     * Update the activity type category.
     *
     * @param  Request $request
     * @param  int $activityTypeCategoryId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $activityTypeCategoryId)
    {
        try {
            $activityTypeCategory = app(UpdateActivityTypeCategory::class)->execute(
                $request->all()
                    +
                    [
                    'account_id' => auth()->user()->account->id,
                    'activity_type_category_id' => $activityTypeCategoryId,
                ]
            );
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        } catch (ValidationException $e) {
            return $this->respondValidatorFailed($e->validator);
        } catch (QueryException $e) {
            return $this->respondInvalidQuery();
        }

        return new ActivityTypeCategoryResource($activityTypeCategory);
    }

    /**
     * Delete an activity type category.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $activityTypeCategoryId)
    {
        try {
            app(DestroyActivityTypeCategory::class)->execute([
                'account_id' => auth()->user()->account->id,
                'activity_type_category_id' => $activityTypeCategoryId,
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        } catch (ValidationException $e) {
            return $this->respondValidatorFailed($e->validator);
        } catch (QueryException $e) {
            return $this->respondInvalidQuery();
        }

        return $this->respondObjectDeleted((int) $activityTypeCategoryId);
    }
}
