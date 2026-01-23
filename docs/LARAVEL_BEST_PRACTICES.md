# Laravel Architecture Best Practices Resources

This document lists authoritative sources for Laravel architecture best practices that can be used to create architecture rules for Laravel Spotlight.

## Official Laravel Documentation

### Core Architecture Concepts

1. **Laravel Documentation - Architecture Concepts**
   - URL: https://laravel.com/docs/architecture-concepts
   - Covers: Service Container, Service Providers, Facades, Request Lifecycle
   - Best for: Understanding Laravel's core architecture patterns

2. **Laravel Documentation - Directory Structure**
   - URL: https://laravel.com/docs/structure
   - Covers: Recommended directory organization, namespace conventions
   - Best for: Code organization rules

3. **Laravel Documentation - Service Container**
   - URL: https://laravel.com/docs/container
   - Covers: Dependency injection, binding, resolving
   - Best for: Dependency injection patterns

### Specific Feature Documentation

4. **Eloquent ORM Best Practices**
   - URL: https://laravel.com/docs/eloquent
   - Key practices:
     - Use eager loading to prevent N+1 queries
     - Use accessors/mutators for data transformation
     - Use scopes for reusable query logic
     - Avoid mass assignment vulnerabilities

5. **Routing Best Practices**
   - URL: https://laravel.com/docs/routing
   - Key practices:
     - Use controllers instead of closures for complex logic
     - Group related routes with route groups
     - Use resource controllers for CRUD operations
     - Cache routes in production

6. **Controllers Best Practices**
   - URL: https://laravel.com/docs/controllers
   - Key practices:
     - Keep controllers thin (delegate to services)
     - Use form requests for validation
     - Use resource controllers
     - Single responsibility principle

7. **Service Providers**
   - URL: https://laravel.com/docs/providers
   - Key practices:
     - Register services in appropriate providers
     - Use deferred providers when possible
     - Keep providers focused

## Community Best Practices

### Laravel News & Community

8. **Laravel News - Best Practices**
   - URL: https://laravel-news.com/category/best-practices
   - Community-driven best practices articles

9. **Laracasts**
   - URL: https://laracasts.com
   - Video tutorials covering architecture patterns
   - Series: "Laravel From Scratch", "Laravel Best Practices"

10. **Laravel Best Practices Repository**
    - URL: https://github.com/alexeymezenin/laravel-best-practices
    - Comprehensive community-maintained guide
    - 12.2k+ stars, widely referenced
    - Covers: Single responsibility, validation, N+1 queries, mass assignment, naming conventions, and more

### Design Patterns

10. **Service Repository Pattern**
    - Pattern: Separate data access (Repository) from business logic (Service)
    - Benefits: Testability, maintainability, single responsibility
    - Implementation: `app/Services` and `app/Repositories` directories

11. **Action Pattern**
    - Pattern: Single-purpose classes for specific actions
    - Benefits: Very focused, easy to test
    - Implementation: `app/Actions` directory

12. **DTO (Data Transfer Objects)**
    - Pattern: Objects that carry data between processes
    - Benefits: Type safety, validation, clarity
    - Implementation: `app/DataTransferObjects` or `app/DTOs`

## Common Architecture Anti-Patterns to Check

### 1. Fat Controllers
- **Problem**: Controllers with too much business logic
- **Solution**: Extract to services, actions, or jobs
- **Rule**: `LargeControllerRule` ✅ (already implemented)

### 2. Route Closures for Complex Logic
- **Problem**: Closures prevent route caching
- **Solution**: Use controllers
- **Rule**: `RouteClosureUsageRule` ✅ (already implemented)

### 3. Direct DB Queries in Controllers
- **Problem**: Business logic mixed with data access
- **Solution**: Use repositories or models
- **Note**: This is context-dependent and not universally applicable

### 4. Missing Form Requests
- **Problem**: Validation logic in controllers
- **Solution**: Use Form Request classes
- **Rule**: `MissingFormRequestsRule` ✅ (already implemented)

### 5. N+1 Query Problems
- **Problem**: Loading relationships inefficiently
- **Solution**: Use eager loading (`with()`, `load()`)
- **Rule**: `NPlusOneQueriesRule` ✅ (already implemented)

### 6. Missing API Resources
- **Problem**: Inconsistent API responses
- **Solution**: Use API Resources
- **Rule**: `MissingApiResourcesRule` ✅ (already implemented)

### 7. Business Logic in Models
- **Problem**: Fat models with too much responsibility
- **Solution**: Extract to services
- **Potential Rule**: Check model size and method complexity

### 8. Missing Service Layer
- **Problem**: No separation between controllers and models
- **Solution**: Create service classes
- **Note**: This is an architectural choice, not universally required

### 9. Global Scopes Overuse
- **Problem**: Too many global scopes can cause unexpected behavior
- **Solution**: Use local scopes or query scopes
- **Potential Rule**: Count global scopes in models

### 10. Missing Policies/Authorization
- **Problem**: Authorization logic scattered
- **Solution**: Use Policies
- **Potential Rule**: Check if policies exist for protected resources

### 11. Missing Event Listeners
- **Problem**: Business logic triggered directly instead of events
- **Solution**: Use Events and Listeners
- **Potential Rule**: Check for direct method calls that could be events

### 12. Missing Jobs for Long-Running Tasks
- **Problem**: Synchronous execution of heavy tasks
- **Solution**: Use Jobs and Queues
- **Potential Rule**: Check for heavy operations in controllers

## Implemented Rules Based on Best Practices

### ✅ Already Implemented

1. **Missing Form Requests** - `MissingFormRequestsRule`
2. **N+1 Query Problems** - `NPlusOneQueriesRule`
3. **Direct ENV Usage** - `DirectEnvUsageRule`
4. **Queries in Blade Templates** - `QueriesInBladeRule`
5. **Missing Mass Assignment Protection** - `MissingMassAssignmentProtectionRule`
6. **Logic in Routes** - `LogicInRoutesRule`
7. **Large Controllers** - `LargeControllerRule`
8. **Route Closures** - `RouteClosureUsageRule`

### Medium Priority

4. **Model Size Check**
   - Similar to controller size check
   - Suggest extracting to services

5. **Missing Policies**
   - Check if models have corresponding policies
   - Suggest using authorization policies

6. **Missing Jobs for Heavy Operations**
   - Detect potentially long-running operations
   - Suggest using queues

### Lower Priority

7. **Global Scope Count**
   - Check for excessive global scopes
   - Suggest using local scopes

8. **Missing Event Usage**
   - Check for direct method calls that could be events
   - Suggest using event-driven architecture

## Additional Resources

### Books

- **"Laravel: Up & Running"** by Matt Stauffer
- **"Laravel Testing Decoded"** by Jeffrey Way
- **"Refactoring to Collections"** by Adam Wathan

### Code Quality Tools

- **Laravel Pint** - Code style fixer
- **PHPStan/Larastan** - Static analysis
- **PHPUnit** - Testing framework
- **Laravel Telescope** - Debugging and monitoring

### Community Standards

- **PSR Standards** (PSR-1, PSR-2, PSR-4, PSR-12)
- **Laravel Coding Standards**
- **SOLID Principles**
- **DRY (Don't Repeat Yourself)**
- **KISS (Keep It Simple, Stupid)**

## How to Use This Guide

When creating new architecture rules:

1. **Reference Official Docs**: Check Laravel documentation for the feature
2. **Check Community Standards**: Look at Laracasts, Laravel News
3. **Follow SOLID Principles**: Ensure rules promote good design
4. **Keep It Practical**: Focus on actionable suggestions
5. **Be Non-Judgmental**: Use suggestion-based language (as per your refactoring)

## Contributing Rules

When contributing new architecture rules:

- Reference the source of the best practice
- Provide clear recommendations
- Use neutral, helpful language
- Include examples in metadata when helpful
