---
paths:
  - 'packages/*/src/Enums/**'
---

# Enums

## Every model gets a string-backed PolicyEnum
Authorization abilities live in a `<Model>PolicyEnum: string` next to the model's package. Cases are TitleCase (`ViewAny`, `ForceDeleteAny`), values are kebab-case ability strings (`view-any-product`). The matching policy returns this enum from `permissionEnum()`.
