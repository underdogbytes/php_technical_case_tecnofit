<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\DTO\RequestWithdrawalDTO;
use App\Application\UseCase\RequestWithdrawalUseCase;
use App\Presentation\Http\Request\WithdrawRequest;
use Hyperf\HttpServer\Contract\ResponseInterface;

class WithdrawalController
{
    public function __construct(
        private RequestWithdrawalUseCase $useCase,
        private ResponseInterface $response
    ) {}

    public function withdraw(string $accountId, WithdrawRequest $request)
    {
        try {
            $dto = new RequestWithdrawalDTO(
                $accountId,
                $request->input('method'),
                $request->input('pix'),
                (float)$request->input('amount'),
                $request->input('schedule')
            );

            $result = $this->useCase->execute($dto);
            return $this->response->json($result)->withStatus(202);

        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code < 100 || $code > 599) $code = 400;

            return $this->response->json(['error' => $e->getMessage()])->withStatus($code);
        }
    }
}
