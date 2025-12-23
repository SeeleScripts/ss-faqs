# Sakura Store FAQs Plugin

## Overview

A WordPress plugin that provides a **FAQ** custom post type with:

- Secure Custom Fields (SCF) integration for a "likes" counter and optional product relationship.
- Taxonomy `faq-type` with default terms: **General**, **Usuario**, **Envios y Entregas**, **Devoluciones y Cambios**.
- Automatic WooCommerce integration – adds a "Producto" term and a product relationship field when WooCommerce is active.
- Full WPGraphQL support – query FAQs, filter by product, and increment likes via mutation.

## Installation

1. Ensure **Secure Custom Fields (SCF)** (or ACF) and **WPGraphQL** plugins are installed and activated.
2. Upload the `ss-faqs` folder to `wp-content/plugins/`.
3. Activate **Sakura Store FAQs** from the WordPress admin plugins page.

## Features

| Feature                  | Description                                                                                  |
| ------------------------ | -------------------------------------------------------------------------------------------- |
| **Custom Post Type**     | `ss-faqs` – title stores the question, content stores the answer.                            |
| **Taxonomy**             | `faq-type` with default terms (General, Usuario, Envios y Entregas, Devoluciones y Cambios). |
| **Likes Field**          | Numeric field (default 0) managed via SCF.                                                   |
| **Product Relationship** | Post Object field linking to WooCommerce products (only when WooCommerce is active).         |
| **Producto Term**        | Added automatically when WooCommerce is detected.                                            |
| **GraphQL**              | Queries and mutations for FAQs, including `incrementFaqLikes`.                               |

## GraphQL Usage

### Query All FAQs

```graphql
query GetFaqs {
  ssFaqs {
    nodes {
      id
      databaseId
      title
      content
      likes
      faqTypes {
        nodes {
          name
          slug
        }
      }
    }
  }
}
```

### Query FAQs by Product Slug

```graphql
query GetProductFaqs($productSlug: String!) {
  ssFaqs(where: { relatedProductSlug: $productSlug }) {
    nodes {
      title
      content
      likes
      relatedProduct {
        ... on SimpleProduct {
          id
          name
        }
      }
    }
  }
}
```

### Query FAQs by Type Slug

```graphql
query GetFaqsByType($typeSlug: String!) {
  ssFaqs(where: { faqType: $typeSlug }) {
    nodes {
      title
      content
      faqTypes {
        nodes {
          name
          slug
        }
      }
    }
  }
}
```

### Query FAQs by Multiple Types (Include)

Use `faqTypeIn` to filter FAQs that belong to any of the specified taxonomy slugs:

```graphql
query GetFaqsByTypes($typeSlugs: [String!]) {
  ssFaqs(where: { faqTypeIn: $typeSlugs }) {
    nodes {
      title
      content
      faqTypes {
        nodes {
          name
          slug
        }
      }
    }
  }
}
```

**Example variables:**

```json
{
  "typeSlugs": ["general", "envios-y-entregas"]
}
```

### Query FAQs Excluding Specific Types

Use `faqTypeNotIn` to exclude FAQs that belong to specified taxonomy slugs:

```graphql
query GetFaqsExcludingTypes($excludeSlugs: [String!]) {
  ssFaqs(where: { faqTypeNotIn: $excludeSlugs }) {
    nodes {
      title
      content
      faqTypes {
        nodes {
          name
          slug
        }
      }
    }
  }
}
```

**Example variables:**

```json
{
  "excludeSlugs": ["producto"]
}
```

### Combine Include and Exclude Filters

You can use both `faqTypeIn` and `faqTypeNotIn` together:

```graphql
query GetFilteredFaqs {
  ssFaqs(
    where: { faqTypeIn: ["general", "usuario"], faqTypeNotIn: ["producto"] }
  ) {
    nodes {
      title
      content
    }
  }
}
```

### Combined Filter (Product + Type)

```graphql
query GetProductFaqsByType($productSlug: String!, $typeSlug: String!) {
  ssFaqs(where: { relatedProductSlug: $productSlug, faqType: $typeSlug }) {
    nodes {
      title
      content
    }
  }
}
```

### Increment Likes Mutation

```graphql
mutation LikeFaq($id: Int!) {
  incrementFaqLikes(input: { databaseId: $id }) {
    likes
    ssFaq {
      title
    }
  }
}
```

## Verification Steps

1. **Activate Plugin** – go to _Plugins → Activate_.
2. **Check Dependencies** – if SCF is missing, an admin notice appears.
3. **Create FAQ** – _FAQs → Add New_, fill title (question) and content (answer).
4. **Assign Taxonomy** – select a term from the _FAQ Type_ sidebar.
5. **Test GraphQL** – use the WPGraphQL IDE to run the queries above.

## License

GPLv2 or later – see the `LICENSE` file in the repository.
