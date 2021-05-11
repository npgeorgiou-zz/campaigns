# Run the tests
```
docker-compose build && docker-compose up
```

In a new console tab
```
docker-compose exec app bash
composer install
php artisan test
```

# Regarding the completeness of the use cases
I only did the "create campaign" and "get campaigns" cases, the latter in less degree of completeness than specced.
I opted to focus on architecture. The missing features are quite trivial, but I assume you want to know how I think about
software, so this is where the focus is.

# Regarding the fundamental principles
## A brief overview of Clean Architecture
Generally the result is a simplified implementation of the Clean/Onion/Hexagonal Architecture.
The main idea behind these terms is the separation of software in layers, each layer having its own responsibility
while being unaware of the internal workings, or even the existence of other layers. Examples of layers are:
- The I/O layer. It gets input and outputs output in any way its nature allows. 
  Peripheral to the actual system. Examples: Web, Console.
- The Domain Model. In the Heart of the system.
- The Use Cases (abbreviated to UCs from now on) that the system should be doing. In the Heart of the system.
- Low-level Services that implement specific functionalities. Examples are: Cache, FileSystem. Peripheral to the actual system.

When we make something using this approach:
- The practical benefits are modularity, interchangeability, testability, clear boundaries, etch.
- The overhead is constant attention on where stuff belong, and how information gets transferred across boundaries 
so that one layers' details or vocabulary do not leak into another.
 - The mechanism by which we achieve it is Inversion of Control, Interfaces, and translating data from one layer's 
   representation to another's.

## A guide through the implementation of these ideas in this solution
Now, to the solution.
* High level logic of each flow:
    - The I/O layer is the usual Laravel Controller. It collects input from the Request,
      translates it to UC input and delegates further to the Heart of the system (the UC).
    - The UC, besides the input, also gets injected the Services it needs. Of course that is an Interface and not an
      implementation of each Service. We use Laravel's DI mechanism to bring Services into the I/O layer, from where we 
      pass them further to the UCs.
      Using Laravel this way allows us to potentially swap services with a mock during testing.
      The BCs use the input and the Services to do what they have to do, and then return the result to the I/O Layer.
    - The I/O Layer translates that UC response to the most appropriate format for the I/O layer, which is an Http response.
      Observe for example, how in the I/O layer UC Errors are translated to Http Status codes.
      

* Observe how everything related to what we need to do is isolated from the framework in its own directory (/accutics).
  In there we have build owr own word.
    - Our Domain Model (Separate from Eloquent's Domain Model, we use the framework, not marry it.)
    - Our Use Cases.
    - The Errors our UCs might produce.
    - The Services that our Use Cases use to do their work.
    
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
      We call the UC from a Console Task. It is already isolated from I/O details, therefore easy to feed with input.
    - Imagine that during testing we want to replace a Service (maybe some future CRM or Subscription Service that 
      of course shouldn't do stuff during testing). We just mock it in the Laravel DI container.
    - Imagine that our schema is not good enough, so we refactor it. Changes can be isolated in the CampaignRepository,
      because it is the only file that knows about the Eloquent Campaign and the database table. It maps from 
      Eloquent Models to the real thing, the Accutics Models, so the rest of the application does not have to care.
    - Generally: It is easy to isolate parts of the system from each other, and everything has a logical place to be.
    

# Regarding implementation-specific choices I had to make.
The thing that tripped me was that the Domain Model includes Inputs. For such a small case I couldn't see why not have 
them as properties of the Campaign Model.
But, since thats what the description wanted, I went with it.

And here is a good example of how this separation of concerns in layers works.
Our Domain Model has Inputs as separate Models. That what our Accutics world deals with. Spec satisfied. 
But its awkward to do the same for the ORM Models. An extra ORM Model/table is not really needed for now.
In the actual persistence implementation, I choose to have only one table, therefore one Eloquent Model, the Campaign, 
that models the Inputs as properties/columns. We have separated our Domain Model from the ORM Laravel uses.
In the boundaries between our world and the ORM world (the UCs and the DBCampaignRepository), happens the appropriate 
translation between our Domain Models and the Eloquent Domain Models.


# Regarding the testing approach
Endpoints have been tested in quite a lot of detail and success/failure scenarios.
I prefer the functional testing approach than the unit test one, and thats how the tests are written.

Functional tests allow us to not care about the internal implementation of the operations, but only about the 
input that we feed the system, and the consequences this has (in this simple case,these consequences are the http response 
and db changes, in a more advanced scenario everything is already in place for mocking Services and injecting the mocks in the UCs).


Functional tests leave the implementation unlocked and easy to change, while making sure the system behaves as it should while we refactor 
and improve it. They also allow us to test whole flows that involve many endpoint hits, instead of just on an 
endpoint-by-endpoint basis.

The db is of course cleared in between tests and each scenario builds its on universe.
Ideally everything should be created from 0 on each test case, but I didn't have time to fiddle that much 
(user data source is permanent and same for tests and prod).

