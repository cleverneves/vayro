<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Requests\RegisterRenterRequest;
use App\Http\Requests\UpdateRenterProfileRequest;
use App\Http\Resources\RenterResource;
use App\Services\RenterAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RenterController extends Controller
{
    public function __construct(private readonly RenterAccountService $renters)
    {
    }

    public function store(RegisterRenterRequest $request)
    {
        try {
            $renter = $this->renters->register($request->payload());
        } catch (BusinessRuleException $e) {
            return $this->fromBusinessRule($e);
        }

        return (new RenterResource($renter))->response()->setStatusCode(201);
    }

    public function me()
    {
        try {
            $renter = $this->renters->profileFor(auth('api')->user());
        } catch (BusinessRuleException $e) {
            return $this->fromBusinessRule($e);
        }

        return new RenterResource($renter);
    }

    public function updateMe(UpdateRenterProfileRequest $request)
    {
        $actor = auth('api')->user();

        try {
            $renter = $this->renters->profileFor($actor);
            $renter = $this->renters->updateProfile($actor, $renter->id, $request->payload());
        } catch (BusinessRuleException $e) {
            return $this->fromBusinessRule($e);
        }

        return new RenterResource($renter);
    }

    private function fromBusinessRule(BusinessRuleException $e): JsonResponse
    {
        if ($e->domainCode() === BusinessRuleException::EMAIL_TAKEN) {
            throw ValidationException::withMessages([
                'email' => ['O e-mail já está em uso.'],
            ]);
        }

        return match ($e->domainCode()) {
            BusinessRuleException::NOT_FOUND => response()->json([
                'message' => 'Locatário não encontrado.',
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
