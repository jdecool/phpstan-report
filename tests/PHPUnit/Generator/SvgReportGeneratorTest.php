<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Tests\PHPUnit\Generator;

use JDecool\PHPStanReport\Generator\SvgReportGenerator;
use JDecool\PHPStanReport\Runner\ResultCache; // Changed Model to Runner
use JDecool\PHPStanReport\Generator\SortField; // Changed Model to Generator
use NumberFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

final class SvgReportGeneratorTest extends TestCase
{
    private SvgReportGenerator $generator;
    private NumberFormatter|MockObject $formatterMock;

    protected function setUp(): void
    {
        $this->formatterMock = $this->createMock(NumberFormatter::class);
        $this->generator = new SvgReportGenerator($this->formatterMock);
    }

    public function testFormatReturnsSvg(): void
    {
        self::assertSame('svg', SvgReportGenerator::format());
    }

    public function testCanBeDumpedInFileReturnsTrue(): void
    {
        self::assertTrue($this->generator->canBeDumpedInFile());
    }

    public function testGenerateWithNoErrors(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $resultCacheMock = $this->createMock(ResultCache::class);

        $resultCacheMock->expects(self::once())
            ->method('getErrorsMap')
            ->with(SortField::Identifier)
            ->willReturn([]);

        $resultCacheMock->expects(self::never()) // Not strictly needed for "no errors" message, but good for consistency
            ->method('countTotalErrors');

        $svgOutput = $this->generator->generate($inputMock, $resultCacheMock);

        self::assertStringContainsString('<svg xmlns="http://www.w3.org/2000/svg"', $svgOutput);
        self::assertStringContainsString('No errors found.', $svgOutput);
        self::assertStringContainsString('</svg>', $svgOutput);
    }

    public function testGenerateWithAllErrorCountsZero(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $resultCacheMock = $this->createMock(ResultCache::class);

        $errorsMap = ['ErrorTypeA' => 0, 'ErrorTypeB' => 0];
        $resultCacheMock->expects(self::once())
            ->method('getErrorsMap')
            ->with(SortField::Identifier)
            ->willReturn($errorsMap);

        $svgOutput = $this->generator->generate($inputMock, $resultCacheMock);

        self::assertStringContainsString('<svg xmlns="http://www.w3.org/2000/svg"', $svgOutput);
        self::assertStringContainsString('All error counts are zero.', $svgOutput);
        self::assertStringContainsString('</svg>', $svgOutput);
    }

    public function testGenerateWithSomeErrors(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $resultCacheMock = $this->createMock(ResultCache::class);

        $errorsMap = [
            'Error.Foo' => 5,
            'Error.BarBaz' => 10,
            'Another.Error' => 2,
        ];

        $resultCacheMock->expects(self::once())
            ->method('getErrorsMap')
            ->with(SortField::Identifier)
            ->willReturn($errorsMap);

        $svgOutput = $this->generator->generate($inputMock, $resultCacheMock);

        self::assertStringContainsString('<svg xmlns="http://www.w3.org/2000/svg"', $svgOutput);
        self::assertStringContainsString('</svg>', $svgOutput);

        // Check for rects (bars) - count should match number of error types
        self::assertEquals(count($errorsMap), substr_count($svgOutput, '<rect'));

        // Check for text labels - count should match number of error types + 2 for Y-axis
        self::assertEquals(count($errorsMap) + 2, substr_count($svgOutput, '<text'));

        // Check for specific labels and counts
        self::assertStringContainsString('Error.Foo (5)', $svgOutput);
        self::assertStringContainsString('Error.BarBaz (10)', $svgOutput);
        self::assertStringContainsString('Another.Error (2)', $svgOutput);

        // Check for axis lines
        self::assertEquals(2, substr_count($svgOutput, '<line')); // X and Y axis lines

        // Check for Y-axis min/max labels
        self::assertStringContainsString('>0</text>', $svgOutput); // Min Y-axis value
        self::assertStringContainsString('>10</text>', $svgOutput); // Max Y-axis value (max from errorsMap)
    }

    public function testGenerateWithSingleError(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $resultCacheMock = $this->createMock(ResultCache::class);

        $errorsMap = [
            'Single.Error' => 7,
        ];

        $resultCacheMock->expects(self::once())
            ->method('getErrorsMap')
            ->with(SortField::Identifier)
            ->willReturn($errorsMap);

        $svgOutput = $this->generator->generate($inputMock, $resultCacheMock);

        self::assertStringContainsString('<svg xmlns="http://www.w3.org/2000/svg"', $svgOutput);
        self::assertStringContainsString('</svg>', $svgOutput);
        self::assertEquals(1, substr_count($svgOutput, '<rect'));
        self::assertEquals(1 + 2, substr_count($svgOutput, '<text')); // 1 label + 2 Y-axis
        self::assertStringContainsString('Single.Error (7)', $svgOutput);
        self::assertStringContainsString('>7</text>', $svgOutput); // Max Y-axis value
    }

    public function testHtmlSpecialCharsInIdentifier(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $resultCacheMock = $this->createMock(ResultCache::class);

        $errorsMap = [
            'Error with <tag> & "quotes"' => 5,
        ];

        $resultCacheMock->expects(self::once())
            ->method('getErrorsMap')
            ->with(SortField::Identifier)
            ->willReturn($errorsMap);

        $svgOutput = $this->generator->generate($inputMock, $resultCacheMock);
        self::assertStringContainsString('Error with &lt;tag&gt; &amp; &quot;quotes&quot; (5)', $svgOutput);
    }
}
