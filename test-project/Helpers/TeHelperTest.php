<?php
use PHPUnit\Framework\TestCase;

class TeHelper extends TestCase
{
    public function testWillExpireAt()
    {

        $dueTime = Carbon::now()->addHours(2);
        $createdAt = Carbon::now();


        $result = TeHelper::testWillExpireAt($dueTime, $createdAt);


        $difference = $dueTime->diffInHours($createdAt);
        if ($difference <= 90) {
            $expectedResult = $dueTime;
        } elseif ($difference <= 24) {
            $expectedResult = $createdAt->addMinutes(90);
        } elseif ($difference > 24 && $difference <= 72) {
            $expectedResult = $createdAt->addHours(16);
        } else {
            $expectedResult = $dueTime->subHours(48);
        }

        $this->assertEquals($expectedResult->format('Y-m-d H:i:s'), $result);
    }
}
