<?php

/**
 * Minimal Rule Example - The Cleanest Possible Rule
 *
 * This shows the absolute minimum needed to create a rule.
 * Everything else is auto-detected!
 */

namespace App\Beacon\Rules\Performance;

use Otatechie\Beacon\Rules\AbstractRule;

class MinimalRule extends AbstractRule
{
    // That's it! Just set description and implement scan()
    protected string $description = 'Checks something important';

    public function scan(): array
    {
        // Your logic here
        if ($issueFound) {
            return $this->suggest('Issue found', [
                'recommendation' => 'How to fix it',
            ]);
        }

        return $this->pass('Everything looks good');
    }
}

/**
 * Auto-detected:
 * - id: 'performance.minimal' (from namespace + class name)
 * - category: 'performance' (from namespace)
 * - severity: 'info' (default)
 * - name: 'Minimal' (from class name)
 * - type: 'advisory' (default)
 *
 * Total lines of boilerplate: 0
 * Total properties needed: 1 (description)
 *
 * This is as clean as it gets! 🎉
 */
