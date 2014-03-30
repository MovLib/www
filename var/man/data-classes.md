# Data Classes

**Data Classes** are objects that can interact with the software installed on the server. Each data class should
represent a single entity or a set of entities.

## Sets
A set is a data structure that provides zero, one, or more entities. The most important and basic rules for a set are as
follows:

1. The class name must be singular and match the name of the entity's class.
2. The class name must end with **Set** (e.g. *MovieSet*).
3. The class must extend the abstract set (`\MovLib\Data\AbstractSet`).

Other than that no special rules apply.

## Weblinks

* [Set Wikipedia Article](https://en.wikipedia.org/wiki/Set_%28abstract_data_type%29)
