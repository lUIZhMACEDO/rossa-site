import numpy as np
from scipy.stats import tukey_hsd
import sys
import json


def main():
    if len(sys.argv) != 2:
        print("Usage: python3 tukey_hsd.py <filepath>")
        sys.exit(1)

    filepath = sys.argv[1]

    groups = []
    with open(filepath, "r") as f:
        for line in f:
            group = list(map(float, line.strip().split(",")))
            groups.append(group)

    results = tukey_hsd(*groups)

    # Convert results to JSON format
    num_groups = len(groups)
    comparisons = []

    # Get confidence interval once to avoid recalculating
    ci = results.confidence_interval()

    for i in range(num_groups):
        for j in range(i + 1, num_groups):
            comparisons.append(
                {
                    "group1_index": i,
                    "group2_index": j,
                    "statistic": float(results.statistic[i, j]),
                    "p_value": float(results.pvalue[i, j]),
                    "ci_lower": float(ci.low[i, j]),
                    "ci_upper": float(ci.high[i, j]),
                }
            )

    output = {
        "confidence_level": results._ci_cl,
        "comparisons": comparisons,
    }

    print(json.dumps(output, indent=2))


if __name__ == "__main__":
    main()
