<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use JDecool\PHPStanReport\Runner\ResultCache;
use NumberFormatter;

final class HtmlReportGenerator implements ReportGenerator
{
    public function __construct(
        private readonly NumberFormatter $formatter,
    ) {}

    public function generate(ResultCache $result, SortField $sortBy = SortField::Identifier): string
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
        <td><b>Level</b></td>
        <td>{$result->getLevel()}</td>
      </tr>
      <tr>
        <td><b>Total error(s)</b></td>
        <td>{$this->formatter->format($result->countTotalErrors(), NumberFormatter::DECIMAL)}</td>
      </tr>
      <tr>
        <td><b>Error(s)</b></td>
        <td>{$this->formatter->format($result->countErrors(), NumberFormatter::DECIMAL)}</td>
      </tr>
      <tr>
        <td><b>Locally ignored error(s)</b></td>
        <td>{$this->formatter->format($result->countLocallyIgnoredErrors(), NumberFormatter::DECIMAL)}</td>
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

        $errorsMap = $result->getErrorsMap();
        match ($sortBy) {
            SortField::Identifier => ksort($errorsMap),
            SortField::Counter => arsort($errorsMap),
        };

        foreach ($errorsMap as $identifier => $count) {
            $html .= "<tr><td>$identifier</td><td>{$this->formatter->format($count, NumberFormatter::DECIMAL)}</td></tr>";
        }

        $html .= <<<HTML
      </tbody>
      <tfoot>
        <tr>
          <th><b>Total</b></th>
          <td>{$this->formatter->format($result->countTotalErrors(), NumberFormatter::DECIMAL)}</td>
        </tr>
      </tfoot>
    </table>
HTML;

        foreach ($errorsMap as $identifier => $_) {
            $html .= "<h2>$identifier</h2>";

            $errors = $result->filterByIdentifier($identifier);

            if (!empty($errors)) {
                $html .= '<ul>';
            }

            foreach ($errors as $error) {
                $html .= "<li>{$error->getFile()}:{$error->getLine()} - {$error->getMessage()}</li>";
            }

            if (!empty($errors)) {
                $html .= '</ul>';
            }
        }

        $html .= <<<HTML
  </body>
</html>
HTML;

        return $html;
    }

    public static function format(): string
    {
        return 'html';
    }
}
