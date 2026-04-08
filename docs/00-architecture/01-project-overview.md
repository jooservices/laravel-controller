# Project Overview

The **JOOservices Laravel Controller** package standardizes how Laravel APIs return JSON responses and expose package-level health endpoints.

The package exists to solve four recurring concerns consistently:

- response envelope shape
- pagination metadata and links
- trace correlation and error formatting
- package-provided status routes and versioned route mapping

At a high level, host applications extend `BaseApiController`, while the package centralizes formatting and exception behavior inside traits.