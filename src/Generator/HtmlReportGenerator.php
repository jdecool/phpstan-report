<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use NumberFormatter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class HtmlReportGenerator implements ReportGenerator
{
    public function __construct(
        private readonly NumberFormatter $formatter,
    ) {}

    public function generate(OutputInterface $output, PHPStanResultCache $result): void
    {
        $html = <<<HTML
<html>
  <head>
    <title>PHPStan Report</title>
  </head>
  <body>
    <h1>PHPStan Report</h1>

    <table>
      <tr>
        <td><b>Total error(s)</b></td>
        <td>{$result->countTotalErrors()}</td>
      </tr>
      <tr>
        <td><b>Error(s)</b></td>
        <td>{$result->countErrors()}</td>
      </tr>
      <tr>
        <td><b>Locally ignored error(s)</b></td>
        <td>{$result->countLocallyIgnoredErrors()}</td>
      </tr>
    </table>

    <hr>

    <table>
      <thead>
        <tr>
          <th>Identifier</th>
          <th>Count</th>
        </tr>
      </thead>
      <tbody>
HTML;

        foreach ($result->getErrorsMap() as $identifier => $count) {
            $html .= "<tr><td>$identifier</td><td>{$this->formatter->format($count, NumberFormatter::DECIMAL)}</td></tr>";
        }

        $html .= <<<HTML
      </tbody>
      <tfoot>
        <tr>
          <th><b>Total</b></th>
          <td>{$result->countTotalErrors()}</td>
        </tr>
      </tfoot>
    </table>
  </body>
</html>
HTML;

        $output->writeln($html);
    }

    public static function format(): string
    {
        return 'html';
    }
}
