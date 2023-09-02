<?php

declare(strict_types=1);

namespace App\Infrastructure\Temporal;

use App\Endpoint\Temporal\Activity\NotificationActivity;
use App\Endpoint\Temporal\Workflow\InputDto;
use App\Endpoint\Temporal\Activity\OrderActivity;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use React\Promise\PromiseInterface;
use Spiral\Queue\QueueInterface;
use Temporal\Activity\ActivityOptions;
use Temporal\Common\RetryOptions;
use Temporal\Exception\Failure\ActivityFailure;
use Temporal\Exception\Failure\CanceledFailure;
use Temporal\Worker\ChildWorkflowCancellationType;
use Temporal\Workflow;

final class MakePizzaWorkflow implements \App\Endpoint\Temporal\Workflow\MakePizzaWorkflow
{
    private int $price;
    private bool $vegesGrown = false;
    private InputDto $input;

    public function execute(InputDto $input)
    {
        $this->input = $input;
        // Уведомлять о всех тапах (email, sms, etc)

        try {
            // Рассчитываем стоимость пиццы
            $this->price = yield Workflow::newActivityStub(
                OrderActivity::class,
                ActivityOptions::new()->withRetryOptions(
                    RetryOptions::new()
                        ->withMaximumAttempts(1)
                ),
            )->calculatePrice($input->name);

            yield Workflow::newUntypedActivityStub(
                ActivityOptions::new()->withRetryOptions(
                    RetryOptions::new()
                        ->withMaximumAttempts(100)
                        ->withInitialInterval(CarbonInterval::minutes(15)),
                ),
            )->execute('PaymentService.pay', [$input->customerUuid, $this->price]);

        } catch (CanceledFailure) {
            // TODO: добавить сагу на возмещение денег
            yield $this->notify('Мы подумали... и решили отменить ваш заказ.');
            yield Workflow::newUntypedActivityStub()->execute('Order.cancel', [$input->name]);
            return;
        } catch (ActivityFailure) {
            // Отменяем заказ
            yield Workflow::newUntypedActivityStub()->execute('Order.cancel', [$input->name]);
            return;
        }

        // Проверяем, что сезон посадки овощей
        $currentMonth = Carbon::now()->month;
        while ($currentMonth < 3 || $currentMonth >= 6) {
            yield Workflow::timer(CarbonInterval::month());
            $currentMonth = Carbon::now()->month;
        }

        // Сажаем овощи
        yield $this->notify('Сажаем овощи.');
        $uuid = yield Workflow::newUntypedActivityStub()->execute('Garden.plantVegetables', [$input->ingridients]);
        yield $this->notify('Овощи посажены. Ждём!');

        do {
            yield Workflow::awaitWithTimeout(CarbonInterval::day(), fn () => $this->vegesGrown);

            if ($this->vegesGrown || (yield Workflow::newUntypedActivityStub()->execute('Garden.isReady', [$uuid]))) {
                $this->vegesGrown = true;
            } else {
                yield $this->notify('Овощи ещё растут :З');
            }
        } while (!$this->vegesGrown);

        // Передаём заказ на кухню
        yield $this->notify('Овощи выросли! Передаём на кухню.');
        yield Workflow::newUntypedActivityStub()->execute('Kitchen.cook', [$input->name]);

        // Зарезервировать повара
        yield Workflow::newUntypedActivityStub()->execute('Kitchen.reserveCooker', [$input->name]);
        yield $this->notify('Выбрали вам крутого повара Валеру.');

        // Повар готовит (месит тесто, в духовке полчаса)
        // Доплата 200% + доставка + ндс + на чай повару
        // Доставка
    }

    public function vegesAreGrown()
    {
        $this->vegesGrown = true;
    }

    private function notify(string $message): PromiseInterface
    {
        return Workflow::newActivityStub(NotificationActivity::class)
            ->notify($this->input->customerUuid, $message);
    }
}
