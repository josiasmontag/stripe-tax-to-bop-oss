# Stripe Tax to BOP OSS Converter

Simple CLI tool, written in PHP. It converts the Stripe Tax reports to the CSV format compatible with the BOP (Elster)
online platform. The created CSV file can be imported directly to make the OSSEUST declaration.

### Usage

```
php stripe-tax-to-bop-oss.php tax-summary-export-from-stripe.csv > output.csv
```

Supports multiple input files (if you have multiple Stripe products)

```
php stripe-tax-to-bop-oss.php report1.csv report2.csv ...
```

### Disclaimer

Quick & dirty solution. Check the script and results before using them!