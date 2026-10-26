# Numerical reference suite

Run from the repository root with PHP 8.2 or newer:

```sh
php tests/reference/run.php
```

The runner is deliberately independent of the site bootstrap and database. It checks fixed analytic examples or hand-solvable cases for every OR solver class (LP, integer LP, assignment, game theory, Markov, Monte Carlo, queueing, shortest path, transportation, transshipment, sensitivity), the StatLab descriptive/distribution/hypothesis/regression engines, Quality's capability/control/sampling/Nelson/MSA/Pareto calculations, MCDM's AHP/SAW/TOPSIS/VIKOR calculators, and PdM's built-in availability/failure-rate/reliability formulas.

Deterministic formulas use tight absolute tolerances. The seeded lognormal Monte Carlo case uses a wider tolerance because it compares finite random samples with the requested population moments. A failing assertion prints its name, expected value, and actual value; exit code 1 means at least one reference did not match.

When adding a case, prefer an independently calculated small example, include units and formula in the assertion name or comment, and state a tolerance appropriate to rounding and numerical approximation. Avoid snapshots of formatted HTML or database-backed records in this numerical suite.
