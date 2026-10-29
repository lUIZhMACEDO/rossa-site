#!/usr/bin/env python3
"""
Unified statistical tests script for inferential statistics.
Accepts JSON input via stdin, returns JSON output via stdout.

Input format:
{
  "measurement_id": 123,
  "groups": [
    {"group_id": 1, "values": [1.2, 1.5, 1.3, 1.4]},
    {"group_id": 2, "values": [2.1, 2.3, 2.0, 2.2]}
  ]
}

Output format:
{
  "measurement_id": 123,
  "shapiro_wilk": [...],
  "levene": {...},
  "test_type": "t_test" or "anova",
  "test_variant": "standard" or "welch",
  "test_statistic": float,
  "p_value": float,
  "tukey_hsd": null or [...]
}
"""

import sys
import json
import numpy as np
from scipy import stats


def run_shapiro_wilk(groups):
    """
    Run Shapiro-Wilk normality test for each group.
    Returns list of results per group.
    """
    results = []
    for group in groups:
        group_id = group['group_id']
        values = np.array(group['values'])
        
        # Shapiro-Wilk requires at least 3 samples
        # should have 3 - checked in ProcessInferentialStatistics
        if len(values) < 3:
            results.append({
                'group_id': group_id,
                'statistic': None,
                'p_value': None,
                'is_normal': None,
                'error': 'Insufficient data (n < 3)'
            })
            continue
        
        try:
            statistic, p_value = stats.shapiro(values)
            # Typically use alpha = 0.05 for normality assumption
            is_normal = p_value > 0.05
            
            results.append({
                'group_id': group_id,
                'statistic': float(statistic),
                'p_value': float(p_value),
                'is_normal': bool(is_normal)
            })
        except Exception as e:
            results.append({
                'group_id': group_id,
                'statistic': None,
                'p_value': None,
                'is_normal': None,
                'error': str(e)
            })
    
    return results


def run_levene_test(groups):
    """
    Run Levene's test for equality of variances.
    Returns test statistic and p-value.
    """
    samples = [np.array(group['values']) for group in groups]
    
    try:
        statistic, p_value = stats.levene(*samples)
        # if p < 0.05, reject null hypothesis of equal variances
        equal_variance = p_value > 0.05
        
        return {
            'statistic': float(statistic),
            'p_value': float(p_value),
            'equal_variance': bool(equal_variance)
        }
    except Exception as e:
        return {
            'statistic': None,
            'p_value': None,
            'equal_variance': None,
            'error': str(e)
        }


def run_t_test(groups, equal_variance):
    """
    Run t-test (standard or Welch's based on Levene's result).
    Returns test type, variant, statistic, and p-value.
    """
    group1_values = np.array(groups[0]['values'])
    group2_values = np.array(groups[1]['values'])
    
    try:
        # use equal_var parameter based on Levene's test result
        statistic, p_value = stats.ttest_ind(
            group1_values, 
            group2_values, 
            equal_var=equal_variance
        )
        
        return {
            'test_type': 't_test',
            'test_variant': 'standard' if equal_variance else 'welch',
            'test_statistic': float(statistic),
            'p_value': float(p_value),
            'tukey_hsd': None,
            'group1_id': groups[0]['group_id'],
            'group2_id': groups[1]['group_id'],
            'n1': len(group1_values),
            'n2': len(group2_values)
        }
    except Exception as e:
        return {
            'test_type': 't_test',
            'test_variant': 'standard' if equal_variance else 'welch',
            'test_statistic': None,
            'p_value': None,
            'tukey_hsd': None,
            'error': str(e)
        }


def run_anova(groups):
    """
    Run one-way ANOVA.
    Returns F-statistic and p-value.
    """
    samples = [np.array(group['values']) for group in groups]
    
    try:
        f_statistic, p_value = stats.f_oneway(*samples)
        
        return {
            'f_statistic': float(f_statistic),
            'p_value': float(p_value)
        }
    except Exception as e:
        return {
            'f_statistic': None,
            'p_value': None,
            'error': str(e)
        }


def run_tukey_hsd(groups):
    """
    Run Tukey HSD post-hoc test.
    Returns pairwise comparisons.
    """
    samples = [np.array(group['values']) for group in groups]
    
    try:
        result = stats.tukey_hsd(*samples)
        
        # get confidence interval
        ci = result.confidence_interval()
        
        # build pairwise comparisons
        comparisons = []
        num_groups = len(groups)
        
        for i in range(num_groups):
            for j in range(i + 1, num_groups):
                comparisons.append({
                    'group1_id': groups[i]['group_id'],
                    'group2_id': groups[j]['group_id'],
                    'group1_index': i,
                    'group2_index': j,
                    'statistic': float(result.statistic[i, j]),
                    'p_value': float(result.pvalue[i, j]),
                    'ci_lower': float(ci.low[i, j]),
                    'ci_upper': float(ci.high[i, j])
                })
        
        return {
            'confidence_level': result._ci_cl,
            'comparisons': comparisons
        }
    except Exception as e:
        return {
            'error': str(e)
        }


def process_measurement(data):
    """
    Process a single measurement with all statistical tests.
    """
    measurement_id = data['measurement_id']
    groups = data['groups']
    num_groups = len(groups)
    
    # init result
    result = {
        'measurement_id': measurement_id,
        'shapiro_wilk': [],
        'levene': {},
        'test_type': None,
        'test_variant': None,
        'test_statistic': None,
        'p_value': None,
        'tukey_hsd': None
    }
    
    # run assumption tests
    result['shapiro_wilk'] = run_shapiro_wilk(groups)
    result['levene'] = run_levene_test(groups)
    
    # determine which test to run based on number of groups
    if num_groups == 2:
        # t-test if n = 2
        equal_variance = result['levene'].get('equal_variance', True)
        if equal_variance is None:  # Levene's test failed
            equal_variance = True  # Default to standard t-test
        
        t_test_result = run_t_test(groups, equal_variance)
        result['test_type'] = t_test_result['test_type']
        result['test_variant'] = t_test_result['test_variant']
        result['test_statistic'] = t_test_result['test_statistic']
        result['p_value'] = t_test_result['p_value']
        result['tukey_hsd'] = None
        
        # Add additional t-test metadata
        result['t_test_metadata'] = {
            'group1_id': t_test_result.get('group1_id'),
            'group2_id': t_test_result.get('group2_id'),
            'n1': t_test_result.get('n1'),
            'n2': t_test_result.get('n2')
        }
        
    elif num_groups > 2:
        # ANOVA if n > 2
        anova_result = run_anova(groups)
        result['test_type'] = 'anova'
        result['test_variant'] = None
        result['test_statistic'] = anova_result['f_statistic']
        result['p_value'] = anova_result['p_value']
        
        # only run Tukey HSD if ANOVA is significant (p < 0.05)
        if result['p_value'] is not None and result['p_value'] < 0.05:
            result['tukey_hsd'] = run_tukey_hsd(groups)
        else:
            result['tukey_hsd'] = None
        
        # add ANOVA metadata
        result['anova_metadata'] = {
            'num_groups': num_groups,
            'groups': [{'id': g['group_id'], 'n': len(g['values'])} for g in groups]
        }
    
    return result


def main():
    """
    Main entry point. Reads JSON from stdin, processes, writes JSON to stdout.
    """
    try:
        # read input from stdin
        input_data = json.load(sys.stdin)
        
        # batch processing
        if 'measurements' in input_data:
            results = []
            for measurement in input_data['measurements']:
                try:
                    result = process_measurement(measurement)
                    results.append(result)
                except Exception as e:
                    # add error
                    results.append({
                        'measurement_id': measurement.get('measurement_id'),
                        'error': str(e),
                        'shapiro_wilk': [],
                        'levene': {},
                        'test_type': None,
                        'test_variant': None,
                        'test_statistic': None,
                        'p_value': None,
                        'tukey_hsd': None
                    })
            
            # output batch results
            print(json.dumps({'results': results}, indent=2))
        else:
            # deprecated - single measurement
            result = process_measurement(input_data)
            print(json.dumps(result, indent=2))
        
        return 0
        
    except json.JSONDecodeError as e:
        error_result = {
            'error': 'Invalid JSON input',
            'details': str(e)
        }
        print(json.dumps(error_result), file=sys.stderr)
        return 1
        
    except Exception as e:
        error_result = {
            'error': 'Processing failed',
            'details': str(e)
        }
        print(json.dumps(error_result), file=sys.stderr)
        return 1


if __name__ == '__main__':
    sys.exit(main())