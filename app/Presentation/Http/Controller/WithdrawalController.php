<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\UseCase\RequestWithdrawalUseCase;
use App\Presentation\Http\Request\WithdrawRequest;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Annotation\Valid;
use App\Application\DTO\RequestWithdrawalDTO;

class WithdrawalController
{
    public function __construct(
        private RequestWithdrawalUseCase $useCase,
        private ResponseInterface $response,
        private ValidatorFactoryInterface $validationFactory
    ) {}

    public function withdraw(string $accountId, WithdrawRequest $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(), 
            $request->rules(), 
            $request->messages()
        );

        if ($validator->fails()) {
            return $this->response->json([
                'error' => $validator->errors()->first()
            ])->withStatus(422);
        }
        
        try {
            // Valide se os inputs básicos existem antes de instanciar o DTO
            $method = $request->input('method');
            $pixData = $request->input('pix', []);
            $amount = (float)$request->input('amount', 0);
            $schedule = $request->input('schedule');

            $dto = new RequestWithdrawalDTO(
                $accountId,
                $method,
                $pixData,
                $amount,
                $schedule
            );

            $result = $this->useCase->execute($dto);
            
            return $this->response->json($result)->withStatus(201);

        } catch (\Throwable $e) {
            
            $code = (int)$e->getCode();
            if ($code < 400 || $code > 599) {
                $code = 500;
            }

            return $this->response->json([
                'error' => $e->getMessage(),
            ])->withStatus($code);
        }
    }
}