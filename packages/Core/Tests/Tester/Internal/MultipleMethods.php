<?php

namespace Beapp\RepositoryTester\Tester\Internal;

class MultipleMethods
{

    public function noTypeDefined($value)
    {
        // This method is only used for reflection test. @see methodProvider above.
    }

    public function typeInCodeOnly(string $value)
    {
        // This method is only used for reflection test. @see methodProvider above.
    }

    /**
     * @param string|int $value
     */
    public function typeInCommentOnly($value)
    {
        // This method is only used for reflection test. @see methodProvider above.
    }

    /**
     * @param string $value
     */
    public function typeInCodeAndComment(string $value)
    {
        // This method is only used for reflection test. @see methodProvider above.
    }

    /**
     * @param string[] $value
     */
    public function mixedUpTypeBetweenCodeAndComment(array $value)
    {
        // This method is only used for reflection test. @see methodProvider above.
    }

    /**
     * @param int $value1
     * @param SimpleObject $value2
     * @param string|null $value3
     * @param array $value4
     */
    public function multipleTypes(int $value1, SimpleObject $value2, ?string $value3, array $value4 = [])
    {
        // This method is only used for reflection test. @see methodProvider above.
    }

}