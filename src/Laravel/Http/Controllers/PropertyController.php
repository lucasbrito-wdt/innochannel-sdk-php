<?php

namespace Innochannel\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Innochannel\Laravel\Traits\HandlesInnochannelExceptions;
use Innochannel\Sdk\Services\PropertyService;

class PropertyController extends Controller
{
    use HandlesInnochannelExceptions;

    protected PropertyService $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    /**
     * Create a new property
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $propertyData = $request->all();
            $property = $this->propertyService->create($propertyData);

            return response()->json([
                'message' => 'Property created successfully',
                'data' => $property
            ], 201);

        } catch (\Throwable $exception) {
            // Try to handle Innochannel exceptions first
            $response = $this->handleInnochannelException($exception);
            if ($response) {
                return $response;
            }

            // If not an Innochannel exception, re-throw for Laravel to handle
            throw $exception;
        }
    }

    /**
     * Update an existing property
     *
     * @param Request $request
     * @param string $propertyId
     * @return JsonResponse
     */
    public function update(Request $request, string $propertyId): JsonResponse
    {
        try {
            $propertyData = $request->all();
            $property = $this->propertyService->update($propertyId, $propertyData);

            return response()->json([
                'message' => 'Property updated successfully',
                'data' => $property
            ]);

        } catch (\Throwable $exception) {
            // Try to handle Innochannel exceptions first
            $response = $this->handleInnochannelException($exception);
            if ($response) {
                return $response;
            }

            // If not an Innochannel exception, re-throw for Laravel to handle
            throw $exception;
        }
    }

    /**
     * Create a room for a property
     *
     * @param Request $request
     * @param string $propertyId
     * @return JsonResponse
     */
    public function createRoom(Request $request, string $propertyId): JsonResponse
    {
        try {
            $roomData = $request->all();
            $room = $this->propertyService->createRoom($propertyId, $roomData);

            return response()->json([
                'message' => 'Room created successfully',
                'data' => $room
            ], 201);

        } catch (\Throwable $exception) {
            // Try to handle Innochannel exceptions first
            $response = $this->handleInnochannelException($exception);
            if ($response) {
                return $response;
            }

            // If not an Innochannel exception, re-throw for Laravel to handle
            throw $exception;
        }
    }

    /**
     * Update a room
     *
     * @param Request $request
     * @param string $propertyId
     * @param string $roomId
     * @return JsonResponse
     */
    public function updateRoom(Request $request, string $propertyId, string $roomId): JsonResponse
    {
        try {
            $roomData = $request->all();
            $room = $this->propertyService->updateRoom($propertyId, $roomId, $roomData);

            return response()->json([
                'message' => 'Room updated successfully',
                'data' => $room
            ]);

        } catch (\Throwable $exception) {
            // Try to handle Innochannel exceptions first
            $response = $this->handleInnochannelException($exception);
            if ($response) {
                return $response;
            }

            // If not an Innochannel exception, re-throw for Laravel to handle
            throw $exception;
        }
    }

    /**
     * Create a rate plan for a room
     *
     * @param Request $request
     * @param string $propertyId
     * @param string $roomId
     * @return JsonResponse
     */
    public function createRatePlan(Request $request, string $propertyId, string $roomId): JsonResponse
    {
        try {
            $ratePlanData = $request->all();
            $ratePlan = $this->propertyService->createRatePlan($propertyId, $roomId, $ratePlanData);

            return response()->json([
                'message' => 'Rate plan created successfully',
                'data' => $ratePlan
            ], 201);

        } catch (\Throwable $exception) {
            // Try to handle Innochannel exceptions first
            $response = $this->handleInnochannelException($exception);
            if ($response) {
                return $response;
            }

            // If not an Innochannel exception, re-throw for Laravel to handle
            throw $exception;
        }
    }
}