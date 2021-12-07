# Masters Cloud Computing Module - code repository.

This codebase is for the Cloud Computing distance learning module of the MSc in Software Development.

The original project was implemented as one git repository per directory - this is stored in one large repository for convenience.
Each directory has its own Dockerfile from which images are built for each microservice. Only backend microservices were required to be unit tested.

Languages used; HTTP, CSS, JavaScript, PHP, Docker. Originally hosted on a provided Rancher cluster on the University private cloud.

Common Test Dependencies;<br>
- PHPUnit 7?<br>
- HTTPGuzzle library for CI tests
