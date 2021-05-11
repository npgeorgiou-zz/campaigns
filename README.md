# Run the tests
```
docker-compose up
```
In a new console tab
```
docker-compose exec app bash
composer install
php artisan test
```

# Regarding the completeness of the use cases
I only did the "create campaign" and "get campaigns" cases, the latter in less degree of completeness than desired.
I opted to focus on architecture. The missing features are quite trivial, 
but the fundamental structure is what makes software be software (malleable) as opposed to hardware.
Of course this consideration is not relevant for this trow-away exercise, but that how I approached it:

# Regarding the fundamental principles
Generally the result is a simplified implementation of the Clean/Onion/Hexagonal Architecture.
The main idea behind these terms is the separation of software in layers, each layer having its own responsibility
while being unaware of the internal workings, or even the existence of other layers. Examples of layers are:
- The I/O layer. It gets input and outputs output in any way its nature allows. Peripheral to the actual system.
- The Domain Model. In the Heart of te system.
- The Use Cases (abbreviated to UCs from now on) that the system should be doing. In the Heart of te system.
- Low-level Services that implement specific functionalities. Examples are: Cache, FileSystem. Peripheral to the actual system.

When we make something using this approach:
- The practical benefits are modularity, replaceability, testability, clear boundaries, etch.
- The overhead is constant attention on where stuff belong, and how information gets transferred across boundaries 
so that one layers' details or vocabulary do not leak into another.
 - The mechanism by which we achieve it is Inversion of Control, Interfaces, and translating data from one layer's 
   representation to another's.

Now, to the solution.
* High level logic of each flow:
    - The I/O layer is the usual Laravel Controller. It collects input from the Request,
      translates it to UC input and delegates further to the Heart of the system, the UC).
    - The UC, besides the input, also gets injected the Services it needs. Of course that is an Interface and not an
      implementation of each Service. We use Laravel's DI mechanism to bring Services into the I/O layer, from where we 
      pass them further to the UCs.
      Using Laravel this way allows us to potentially swap services with a mock during testing.
      They BCs use the input and the Services to do what they have to do, and then return the result to the I/O Layer.
    - The I/O Layer translates that UC response to the most appropriate format for the I/O layer, which is an Http response.
      Observe for example, how in the I/O layer, how UC Errors are translated to Http Status codes.
      

* Observe how everything related to what we need to do is isolated from the framework in its own directory (/accutics).
  In there we have build owr own word.
    - Our Domain Model (Separate from Eloquent's Domain Model, we use the framework, not marry it.)
    - Our Use Cases.
    - The Errors our UCs might produce.
    - The Services (low-level operations that our Use Cases use to do their work).
    
* Observe how nothing in our world has any hard dependency on the framework (or any potential future library).
    - Cases do not depend on Laravel Requests
    - Cases do not return Laravel Responses
    - Nothing in our world knows anything about Laravel and Eloquent, and definitely nothing about a database or any 
      other storage mechanism.
    - Repositories do not expose their implementation in their API. They do not accept nor return Eloquent Models. 
      They use them in their internal implementations, but their API deals only with Accutics Models.

* We have managed to isolate the real thing our system does (the what) from the framework/tools (the how).

# Elaboration on the practical benefits of this design philosophy on *larger* systems
> We may therefore picture the process of form-making as the action of a series of subsystems, all interlinked,
> yet sufficiently free of one another to adjust independently in a feasible amount of time. It works, because
> the cycles of correction and recorrection, which occur during adaptation, are  restricted to one subsystem at a time.

*Christofer Alexander, 1973. Notes on the Synthesis of Form*

Christofer Alexander is not a programmer and he is talking about the process of designing physical systems, 
like teapots, houses, and cities. I find it fascinating that the same principles can be applied to programming.

* Let's imagine some benefits of this separation/modularity/IoC:
    - Imagine that we want to start saving Users not in files but in Google Cloud. 
      We make a new Class that implements the UserRepository.
    - Imagine that some UCs need to happen from, or also from another I/O layer, like the Console. 
      We call the UC from a Console Task. It is already isolated from I/O details, so easy to feed with input.
    - Imagine that during testing we want to replace a Service (maybe the CampaignRepository, maybe some future CRM 
      or Subscription Service that of course shouldn't do stuff during testing). 
      We just mock it in the Laravel DI container.
    - Imagine that our schema is not good enough, so we refactor it. Changes can be isolated in the CampaignRepository,
      because it is the only file that knows about the Eloquent Campaign and the database table. It maps from 
      Eloquent Models to the real thing, the Accutics Models, so the rest of the application does not have to care.
    - Generally: It is easy to isolate parts of the system from each other, and everything has a logical place to be.
    

# Regarding implementation-specific choices I had to make.
The thing that tripped me was that the Domain Model includes Inputs. For such a small case I couldnt see why not have 
them as properties of the Campaign Model.
But, since thats what the description wanted, I went with it.

And here is a good example of how this separation of concerns in layers, each with its own implementation, works.
Our Domain Model, has Inputs as separate Models. But the persistence implementation, that is, whatever happens inside 
the CampaignRepository implementation, uses the Eloquent Models.
The Eloquent Domain Model has only one Model, the Campaign, that models the Inputs as properties/columns.
In the boundaries between our world and the ORM world (the UCs and the DBCampaignRepositoty), the appropriate 
translation between our Domain Models and the Eloquent Domain Models happen.


# Regarding the testing approach
Endpoints have been tested in quite a lot of detail and success/failure scenarios.
I prefer the functional testing approach than the unit test one, and thats how the tests are written.

Functional tests allow us to not care about the internal implementation of the operations, but only about the 
input that we feed the system, and the consequences this has (in this simple case,these consequences are the http respose 
and db changes).


It leaves the implementation unlocked and easy to change, while making sure the system behaves as it should while we refactor 
and improve it. It also allows us to test whole user flows that involve many endpoint hits, instead of just an 
endpoint-by-endpoint basis.

Db is of course cleared in between tests and each scenario builds its on universe.
Ideally everything should be created from 0 on each test case, but I didnt have time to fiddle that much 
(user data source is permanent and same for tests and prod).

