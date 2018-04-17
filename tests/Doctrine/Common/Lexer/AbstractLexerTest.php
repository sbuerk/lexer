<?php

namespace Doctrine\Tests\Common\Lexer;

class AbstractLexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConcreteLexer
     */
    private $concreteLexer;

    public function setUp()
    {
        $this->concreteLexer = new ConcreteLexer();
    }

    public function dataProvider()
    {
        return array(
            array(
                'price=10',
                array(
                    array(
                        'index' => 0,
                        'value' => 'price',
                        'type' => 'string',
                        'position' => 0,
                    ),
                    array(
                        'index' => 1,
                        'value' => '=',
                        'type' => 'operator',
                        'position' => 5,
                    ),
                    array(
                        'index' => 2,
                        'value' => 10,
                        'type' => 'int',
                        'position' => 6,
                    ),
                ),
            ),
        );
    }


    /**
     * @dataProvider dataProvider
     *
     * @param $input
     * @param $expectedTokens
     */
    public function testMoveNext($input, $expectedTokens)
    {
        $this->concreteLexer->setInput($input);
        $this->assertNull($this->concreteLexer->lookahead);

        for ($i = 0; $i < count($expectedTokens); $i++) {
            $this->assertTrue($this->concreteLexer->moveNext());
            $this->assertEquals($expectedTokens[$i], $this->concreteLexer->lookahead);
        }

        $this->assertFalse($this->concreteLexer->moveNext());
        $this->assertNull($this->concreteLexer->lookahead);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $input
     * @param $expectedTokens
     */
    public function testPeek($input, $expectedTokens)
    {
        $this->concreteLexer->setInput($input);
        foreach ($expectedTokens as $expectedToken) {
            $this->assertEquals($expectedToken, $this->concreteLexer->peek());
        }

        $this->assertNull($this->concreteLexer->peek());
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $input
     * @param $expectedTokens
     */
    public function testGlimpse($input, $expectedTokens)
    {
        $this->concreteLexer->setInput($input);

        foreach ($expectedTokens as $expectedToken) {
            $this->assertEquals($expectedToken, $this->concreteLexer->glimpse());
            $this->concreteLexer->moveNext();
        }

        $this->assertNull($this->concreteLexer->peek());
    }

    public function inputUntilPositionDataProvider()
    {
        return array(
            array('price=10', 5, 'price'),
        );
    }

    /**
     * @dataProvider inputUntilPositionDataProvider
     *
     * @param $input
     * @param $position
     * @param $expectedInput
     */
    public function testGetInputUntilPosition($input, $position, $expectedInput)
    {
        $this->concreteLexer->setInput($input);

        $this->assertSame($expectedInput, $this->concreteLexer->getInputUntilPosition($position));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $input
     * @param $expectedTokens
     */
    public function testIsNextToken($input, $expectedTokens)
    {
        $this->concreteLexer->setInput($input);

        $this->concreteLexer->moveNext();
        for ($i = 0; $i < count($expectedTokens); $i++) {
            $this->assertTrue($this->concreteLexer->isNextToken($expectedTokens[$i]['type']));
            $this->concreteLexer->moveNext();
        }
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $input
     * @param $expectedTokens
     */
    public function testIsNextTokenAny($input, $expectedTokens)
    {
        $allTokenTypes = array_map(function ($token) {
            return $token['type'];
        }, $expectedTokens);

        $this->concreteLexer->setInput($input);

        $this->concreteLexer->moveNext();
        for ($i = 0; $i < count($expectedTokens); $i++) {
            $this->assertTrue($this->concreteLexer->isNextTokenAny(array($expectedTokens[$i]['type'])));
            $this->assertTrue($this->concreteLexer->isNextTokenAny($allTokenTypes));
            $this->concreteLexer->moveNext();
        }
    }

    public function testGetLiteral()
    {
        $this->assertSame('Doctrine\Tests\Common\Lexer\ConcreteLexer::INT', $this->concreteLexer->getLiteral('int'));
    }

    public function testIsA()
    {
        $this->assertTrue($this->concreteLexer->isA(11, 'int'));
        $this->assertTrue($this->concreteLexer->isA(1.1, 'int'));
        $this->assertTrue($this->concreteLexer->isA('=', 'operator'));
        $this->assertTrue($this->concreteLexer->isA('>', 'operator'));
        $this->assertTrue($this->concreteLexer->isA('<', 'operator'));
        $this->assertTrue($this->concreteLexer->isA('fake_text', 'string'));
    }
}