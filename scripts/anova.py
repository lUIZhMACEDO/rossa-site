import numpy as np
from scipy import stats
import sys


def main():
    if len(sys.argv) != 2:
        print("Usage: python3 anova.py <filepath>")
        sys.exit(1)

    filepath = sys.argv[1]

    groups = []
    with open(filepath, 'r') as f:
        for line in f:
            group = list(map(float, line.strip().split(',')))
            groups.append(group)

    f_statistic, p_value = stats.f_oneway(*groups)
    print(f"F-statistic: {f_statistic}, p-value: {p_value}")


if __name__ == "__main__":
    main()