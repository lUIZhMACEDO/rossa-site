## Downloading/Loading Genes (Alliance Database)

All genome data loaded into our `genes` database table is sourced from the Alliance of Genome Resources (https://www.alliancegenome.org/downloads). Follow the steps below to download and load gene description data for various species.

### Download Genes from Alliance

Use the following command to download gene TSV files:

```bash
php artisan rossa:load-genes
```

> This command will **dump the current gene table**, and download/parse the following resources:

https://fms.alliancegenome.org/download/GENE-DESCRIPTION-TSV_HUMAN.tsv.gz
https://fms.alliancegenome.org/download/GENE-DESCRIPTION-TSV_MGI.tsv.gz
https://fms.alliancegenome.org/download/GENE-DESCRIPTION-TSV_RGD.tsv.gz

The data we collect from these .tsv files are as follows:

-   Alliance Gene ID (eg: HGNC:5358)
-   Gene Symbol (eg: IRF8)

### Lazy Loading additional details

When the user searches for genes, we search our database for a matching gene symbol. When the user selects a gene from the search results, we lazy load additional details from the Alliance API.
