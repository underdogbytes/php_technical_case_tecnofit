<?php
declare(strict_types=1);

namespace App\Presentation\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use App\Application\UseCase\ProcessScheduledWithdrawalsUseCase;

#[Command]
class ProcessWithdrawalsCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('app:process-withdrawals');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Process pending scheduled withdrawals.');
    }

    public function handle()
    {
        $this->info('Starting processing scheduled withdrawals...');
        $useCase = $this->container->get(ProcessScheduledWithdrawalsUseCase::class);
        $useCase->execute();
        $this->info('Finished processing.');
    }
}
