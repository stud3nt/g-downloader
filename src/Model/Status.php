<?php

namespace App\Model;

use App\Annotation\Serializer\ObjectVariable;
use App\Enum\StatusCode;
use App\Factory\RedisFactory;

class Status extends AbstractModel
{
    /**
     * @ObjectVariable(type="integer")
     */
    public int $code = StatusCode::NoEffect;

    /**
     * @ObjectVariable(type="integer")
     */
    public int $progress = 0;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $description = '';

    /** @var \Predis\ClientInterface|\Redis|\RedisCluster */
    protected $redis;

    protected ?string $requestIdentifier = null;

    protected ?array $steppedProgressData = [];

    public function __construct()
    {
        $this->redis = (new RedisFactory())->initializeConnection();
    }

    /**
     * @param int $initialProgress
     * @param string|null $initialDescription
     * @throws \Exception
     * @return ParserRequest
     * @throws \ReflectionException
     */
    public function start(int $initialProgress = 0, string $initialDescription = null): self
    {
        $this->setCode(StatusCode::OperationStarted);
        $this->setProgress($initialProgress);
        $this->setDescription($initialDescription);

        $this->send(5);

        return $this;
    }

    /**
     * @param int $progress
     * @param string|null $description
     * @param bool $send
     * @return ParserRequest
     * @throws \ReflectionException
     */
    public function updateProgress(int $progress = 0, string $description = null, bool $send = false): self
    {
        $this->setCode(StatusCode::OperationInProgress);
        $this->setProgress($progress);
        $this->setDescription($description);

        if ($send)
            $this->send();

        return $this;
    }

    /**
     * @param string|null $description
     * @param bool $send
     * @return ParserRequest
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function end(string $description = null, bool $send = true): self
    {
        $this->setCode(StatusCode::OperationEnded);
        $this->setProgress(100);
        $this->setDescription($description);

        if ($send)
            $this->send(6);

        return $this;
    }

    /**
     * @param string $progressName
     * @param int $totalValue
     * @param int $startPercent
     * @param int $endPercent
     * @throws \Exception
     */
    public function startSteppedProgress(string $progressName, int $totalValue, int $startPercent = 10, int $endPercent = 100): void
    {
        $this->steppedProgressData[$progressName] = [
            'startPercent' => $startPercent,
            'endPercent' => $endPercent,
            'totalValue' => $totalValue,
            'currentValue' => 0,
            'stepSize' => 1
        ];

        $this->updateProgress($startPercent);
    }

    /**
     * @param string $progressName
     * @throws \Exception
     */
    public function executeSteppedProgressStep(string $progressName, string $description = null) : void
    {
        $progressData = $this->steppedProgressData[$progressName];
        $newValue = $progressData['currentValue'] + $progressData['stepSize'];

        $rangeSize = $progressData['endPercent'] - $progressData['startPercent']; // calculate percent range size
        $calculatedProgressValue = round((($newValue / $progressData['totalValue']) * $rangeSize), 2);
        $calculatedProgressValue += $progressData['startPercent'];

        $this->steppedProgressData[$progressName]['currentValue'] = $newValue;

        $this->updateProgress($calculatedProgressValue, $description)->send();
    }

    /**
     * @param string $progressName
     * @throws \Exception
     */
    public function endSteppedProgress(string $progressName) : void
    {
        $this->updateProgress(
            $this->steppedProgressData[$progressName]['endPercent']
        )->send();

        unset($this->steppedProgressData[$progressName]);
    }

    /**
     * @return ParserRequest
     * @throws \ReflectionException
     */
    public function markAsDuplicated(): self
    {
        $this->setCode(StatusCode::DuplicatedOperation);

        return $this;
    }

    /**
     * @throws \ReflectionException
     */
    public function checkIfRequestDuplicated()
    {
        if ($data = $this->read())
            if ($data['code'] !== StatusCode::OperationEnded)
                return true;

        return false;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return $this;
     */
    public function setCode(int $code = StatusCode::NoEffect): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return int
     */
    public function getProgress(): int
    {
        return $this->progress;
    }

    /**
     * @param int $progress
     * @param bool $send
     * @return $this;
     * @throws \ReflectionException
     */
    public function setProgress(int $progress, bool $send = false): self
    {
        $this->progress = $progress;

        if ($send)
            $this->send();

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this;
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestIdentifier(): ?string
    {
        return $this->requestIdentifier;
    }

    /**
     * @param string $requestIdentifier
     * @return $this
     */
    public function setRequestIdentifier(string $requestIdentifier): self
    {
        $this->requestIdentifier = $requestIdentifier;

        return $this;
    }

    /**
     * Saves status in Redis/memory
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function send(int $expire = 0)
    {
        if ($this->redis->exists($this->requestIdentifier))
            $this->redis->del($this->requestIdentifier);

        $this->redis->set(
            $this->requestIdentifier,
            json_encode([
                'code' => $this->getCode(),
                'progress' => $this->getProgress(),
                'description' => $this->getDescription()
            ])
        );

        if ($expire > 0)
            $this->redis->expire($this->requestIdentifier, $expire);
    }

    public function clear(): bool
    {
         if ($this->redis->exists($this->requestIdentifier))
             $this->redis->del($this->requestIdentifier);

         return true;
    }

    /**
     * @return array|null
     * @throws \ReflectionException
     */
    public function read(): ?array
    {
        if ($this->redis->exists($this->requestIdentifier)) {
            return json_decode(
                $this->redis->get($this->requestIdentifier), true
            );
        }

        return null;
    }
}