# Elasticsearch Integration Evaluation (Optional)

## Decision

- **Current decision:** keep native database full-text search and faceted filters as the primary engine.
- **Rationale:** current feature set already supports autocomplete, faceted filtering, analytics, and caching without introducing extra operational overhead.

## What Exists Today

- Advanced search endpoints and autocomplete are implemented.
- Search history and analytics are implemented.
- Query caching and DB indexes are in place for common search paths.

## When to Introduce Elasticsearch

Adopt Elasticsearch/OpenSearch only when one or more of these thresholds is reached:

- Product catalog grows beyond ~100k searchable SKUs with heavy multi-facet queries.
- Relevance tuning (synonyms/stemming/boosting) becomes a blocker in SQL search.
- Latency SLOs cannot be maintained with DB + cache strategy during peak traffic.

## Migration Strategy (Deferred Plan)

1. Mirror product index documents (`id`, `name`, `slug`, `category`, `price`, `stock`, `tags`, `ratings`).
2. Add dual-read feature flag for search API.
3. Validate relevance and latency in shadow mode.
4. Switch read path after parity checks.
5. Keep rollback path to SQL search for one release cycle.
