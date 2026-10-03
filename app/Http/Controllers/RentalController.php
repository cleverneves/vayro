<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Requests\ListAvailableVehiclesRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Http\Resources\AvailableVehicleResource;
use App\Http\Resources\RentalResource;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RentalController extends Controller
{
    public function __construct(private readonly RentalService $rentals)
    {
    }

    public function available(ListAvailableVehiclesRequest $request)
    {
        $payload = $request->payload();

        try {
            $vehicles = $this->rentals->listAvailableVehicles(
                auth('api')->user(),
                $payload['starts_on'],
                $payload['day_count'],
            );
        } catch (BusinessRuleException $e) {
            return $this->fromBusinessRule($e);
        }

        return AvailableVehicleResource::collection($vehicles);
    }

    public function store(StoreRentalRequest $request)
    {
        try {
            $rental = $this->rentals->createForRenter(auth('api')->user(), $request->payload());
        } catch (BusinessRuleException $e) {
            return $this->fromBusinessRule($e);
        }

        $rental->load(['renter', 'vehicle.modelo.marca']);

        return (new RentalResource($rental))->response()->setStatusCode(201);
    }

    public function index()
    {
        try {
            $rentals = $this->rentals->listForRenter(auth('api')->user());
        } catch (BusinessRuleException $e) {
            return $this->fromBusinessRule($e);
        }

        return RentalResource::collection($rentals);
    }

    public function show(int $locacao)
    {
        try {
            $rental = $this->rentals->showForRenter(auth('api')->user(), $locacao);
        } catch (BusinessRuleException $e) {
            return $this->fromBusinessRule($e);
        }

        return new RentalResource($rental);
    }

    private function fromBusinessRule(BusinessRuleException $e): JsonResponse
    {
        if ($e->domainCode() === BusinessRuleException::INVALID_PERIOD) {
            throw ValidationException::withMessages([
                'data_inicio' => [$e->getMessage() !== '' ? $e->getMessage() : 'O período informado não é válido.'],
            ]);
        }

        if ($e->domainCode() === BusinessRuleException::INVALID_REASON) {
            throw ValidationException::withMessages([
                'motivo' => [$e->getMessage() !== '' ? $e->getMessage() : 'O motivo informado não é válido.'],
            ]);
        }

        return match ($e->domainCode()) {
            BusinessRuleException::VEHICLE_UNAVAILABLE => response()->json([
                'message' => 'O automóvel não está disponível nesse período.',
            ], 409),
            BusinessRuleException::NOT_FOUND => response()->json([
                'message' => 'Locação não encontrada.',
            ], 404),
            BusinessRuleException::FORBIDDEN => response()->json([
                'message' => 'Acesso recusado.',
            ], 403),
            default => response()->json([
                'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Erro de negócio.',
            ], 409),
        };
    }
}
