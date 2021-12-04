# Proxy Router
## Custom reverse proxy router for Web Word Count application.
Used to parse queries to the entire backend and conceal backend config.

Requirements;
- Should load config from an external file.
- Must faciliate service discovery by admins.
- Must be highly configurable by admins.
- Must be custom built, no external services are allowed.
- Execute unit testing on a CI pipeline (a seperate test_config file is used to prevent service issues when running CI pipeline unit tests).

Documentation is seperate (all HTTP GET keys etc).
Watch demonstration video on my website: https://tom-ni.github.io/
