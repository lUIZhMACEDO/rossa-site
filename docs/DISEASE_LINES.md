## Downloading/Loading Diseases (MONDO Ontology)

### Download Diseases

Use the following command to download a disease JSON file:

```bash
php artisan rossa:load-diseases
```

> This command will download/parse the following resource:

https://purl.obolibrary.org/obo/mondo.json

from site:
`https://mondo.monarchinitiative.org/pages/download/`

The data we collect from this JSON file is as follows:

-   iri (eg: http://purl.obolibrary.org/obo/MONDO_0005148)
-   lbl (eg: "osteogenesis")

### Lazy Loading additional details

When the user searches for diseases, we search our database for a matching disease. When the user selects a disease from the search results, we lazy load additional details from the Mondo API `(www.ebi.ac.uk/ols4/api/ontologies/mondo)`.
