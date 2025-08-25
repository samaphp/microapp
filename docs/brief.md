# Project Brief: MicroApp - PHP Microframework

## Executive Summary

MicroApp is a minimal PHP 7.4+ microframework designed for building super-microservices with clean routing and zero dependencies. The framework targets developers who value simplicity, control, and long-term maintainability over feature richness. MicroApp serves the market of lightweight API development, internal tooling, and focused microservice endpoints where traditional frameworks would introduce unnecessary overhead. The key value proposition is enabling developers to build services that can "live for decades without requiring upgrades or major refactors" while maintaining complete transparency and control over the codebase.

## Problem Statement

Traditional PHP frameworks create significant overhead for simple microservices and API endpoints. Developers face a complex tradeoff: either use heavy frameworks like Laravel/Symfony that introduce hundreds of dependencies, complex abstractions, and frequent breaking changes requiring constant maintenance, or write raw PHP with manual routing that lacks structure and becomes unmaintainable as it grows. 

Current solutions fall short because:
- **Heavy frameworks** burden simple services with unnecessary complexity, slow startup times, and dependency hell
- **Raw PHP** lacks routing structure, leading to messy conditional logic and poor maintainability  
- **Existing micro-frameworks** often still require external dependencies or compromise on long-term stability
- **Enterprise environments** struggle with frameworks that change frequently, breaking existing services

The urgency is driven by the growing microservices architecture trend where teams need to deploy dozens of small, focused services that must remain stable and maintainable for years without constant framework upgrades.

## Proposed Solution

MicroApp solves this by providing a "Goldilocks" solution - just the right amount of framework without the bloat. The core approach centers on:

- **Single-file implementation** (~300 lines) that developers can read, understand, and modify in under an hour
- **Zero external dependencies** eliminating dependency hell and security vulnerabilities from third-party packages
- **Clean routing system** with named parameters, HTTP verb support, and middleware hooks without unnecessary abstractions
- **PSR-4 autoloading compatibility** for organized code structure while maintaining simplicity
- **Stability-first design** with semantic versioning and backward compatibility guarantees

Key differentiators from existing solutions:
- Unlike heavy frameworks: No dependency management, no complex abstractions, no frequent breaking changes
- Unlike raw PHP: Structured routing, middleware support, organized code patterns
- Unlike other micro-frameworks: Truly zero dependencies, commitment to long-term stability, readable source code

The high-level vision is to become the "Swiss Army knife" for PHP microservices - small enough to understand completely, powerful enough for real applications, stable enough for enterprise deployment.

## Target Users

### Primary User Segment: Backend PHP Developers in Microservices Teams

**Demographic/Profile:**
- Mid to senior-level PHP developers (3+ years experience)
- Working in companies adopting microservices architecture (50-5000+ employees)
- Team sizes of 2-10 developers per service
- Budget-conscious environments or startups to enterprise

**Current Behaviors & Workflows:**
- Building 5-20 small, focused API services per quarter
- Spending significant time on framework setup, dependency management, and maintenance
- Struggling with framework upgrade cycles that break existing services
- Copy-pasting boilerplate code across services due to framework complexity

**Specific Needs & Pain Points:**
- Need routing and middleware without framework overhead
- Require services that remain stable for 2-5+ years without forced upgrades
- Want to understand their entire framework stack (security, debugging, customization)
- Need fast deployment and minimal server resource consumption

**Goals:**
- Deploy microservices quickly without complex setup
- Maintain services long-term with minimal ongoing maintenance
- Keep hosting costs low through lightweight resource usage
- Build expertise in a tool they can fully understand and control

## Goals & Success Metrics

### Business Objectives
- **Achieve 1,000+ GitHub stars within 12 months** indicating developer community interest and adoption
- **Reach 100+ monthly Packagist installs within 6 months** demonstrating practical usage in production
- **Maintain 95%+ backward compatibility** across minor version releases to fulfill stability promise
- **Generate 50+ community contributions** (issues, PRs, documentation) showing ecosystem health
- **Establish partnerships with 3+ hosting providers** for optimized deployment recommendations

### User Success Metrics
- **Average setup time under 10 minutes** from `composer require` to first working endpoint
- **Framework understanding time under 2 hours** for experienced PHP developers to read and comprehend source
- **Service deployment time under 5 minutes** from code to production-ready microservice
- **Memory footprint under 2MB** per service instance at runtime
- **Long-term stability: 80%+ of services run for 12+ months** without forced framework upgrades

### Key Performance Indicators (KPIs)
- **Community Growth**: GitHub stars, forks, watchers month-over-month growth rate
- **Adoption Rate**: Packagist downloads and unique projects using MicroApp
- **Developer Satisfaction**: Documentation ratings, tutorial completion rates, support ticket sentiment
- **Stability Metric**: Percentage of users who upgrade to new minor versions within 30 days
- **Performance Benchmark**: Response time and memory usage compared to Laravel/Slim equivalents

## MVP Scope

### Core Features (Must Have)
- **HTTP Method Routing:** Complete support for GET, POST, PUT, DELETE, PATCH with clean syntax and named route parameters like `/user/{id}` and `/product/{id:int}`
- **Middleware System:** Before/after middleware hooks with both global and route-specific registration, auto-discovery from directory, and clean invocation patterns
- **Service Container:** Simple `registerService()` and `getService()` methods for dependency injection without external container complexity
- **Request/Response Handling:** Input sanitization, JSON response helpers, header management, and centralized response lifecycle
- **PSR-4 Autoloading:** Controller auto-discovery, organized code structure, and Composer package compatibility
- **Zero Dependencies:** Complete functionality without requiring any external packages beyond PHP 7.4+ core and ext-json
- **Production Ready:** Error handling, logging integration, basePath support for subdirectory deployments

### Out of Scope for MVP
- Template/view rendering systems (API-focused, not HTML applications)
- Database abstraction or ORM integration
- Authentication/authorization systems (middleware-level implementation)
- Session management
- CSRF protection (application-level responsibility)
- File upload handling
- Rate limiting (proxy/middleware level)
- Configuration file support (.env loading)

### MVP Success Criteria
**MVP is successful when:** A developer can install via Composer, create a working API endpoint with middleware in under 10 minutes, deploy to production with confidence in long-term stability, and understand the entire framework source code in under 2 hours.

## Post-MVP Vision

### Phase 2 Features
- **Route Grouping & Prefixing:** Enable `/api/v1` prefixes and logical route organization for larger applications while maintaining simplicity
- **Route Caching:** Optional route resolution caching for improved performance in high-traffic scenarios without external dependencies
- **Enhanced Middleware:** Middleware chaining, middleware groups (e.g., `adminOnly` applying multiple middlewares), and conditional middleware execution
- **Extended Service Container:** Lazy loading, factory patterns, and service lifecycle management while preserving minimalist philosophy
- **Developer Experience:** Enhanced debugging utilities, route inspection tools, and better error messages
- **PHP 8+ Features:** Leverage attributes for routing, union types, and modern PHP capabilities while maintaining backward compatibility

### Long-term Vision
**Years 1-2:** Establish MicroApp as the de facto choice for PHP microservices, with a thriving ecosystem of community middleware and deployment patterns. Achieve recognition as the "Express.js of PHP" - simple, fast, and widely adopted. Build partnerships with hosting providers for optimized deployment experiences and create comprehensive learning resources.

### Expansion Opportunities
- **MicroApp CLI Tools:** Code generation, project scaffolding, and deployment automation through separate packages
- **Ecosystem Packages:** Optional SQLite integration, file handling utilities, and PSR-15 compatibility layers as separate, opt-in packages
- **Enterprise Features:** Advanced logging, metrics integration, and deployment tooling for large-scale microservice environments
- **Community Platform:** Plugin marketplace, best practices documentation, and case study sharing

## Technical Considerations

### Platform Requirements
- **Target Platforms:** Web servers (Apache, Nginx, LiteSpeed) with PHP 7.4+ support
- **Browser/OS Support:** Server-side only; client compatibility depends on API design (JSON responses work universally)
- **Performance Requirements:** Sub-millisecond routing resolution, under 2MB memory footprint, sub-100ms response times for simple endpoints

### Technology Preferences
- **Frontend:** N/A (API-focused framework; frontend technology agnostic)
- **Backend:** Pure PHP 7.4+ with strict typing, no external dependencies beyond ext-json
- **Database:** Database-agnostic (no built-in ORM/abstraction; developers use PDO, Doctrine, or preferred solutions)
- **Hosting/Infrastructure:** Optimized for shared hosting, VPS, containers, and serverless environments (AWS Lambda, Google Cloud Functions)

### Architecture Considerations
- **Repository Structure:** Single-package Composer library with optional companion dev tools package
- **Service Architecture:** Designed for microservice deployment patterns; each service is independent
- **Integration Requirements:** RESTful API patterns, JSON communication, standard HTTP status codes
- **Security/Compliance:** Input sanitization built-in, HTTPS enforcement recommended, no sensitive data handling in core framework

## Constraints & Assumptions

### Constraints
- **Budget:** Open source project with volunteer/community development model; no dedicated funding for full-time development
- **Timeline:** Organic development pace driven by community needs and maintainer availability; major releases quarterly at most
- **Resources:** Limited to 1-2 core maintainers initially; relies on community contributions for growth and feature development
- **Technical:** Must maintain PHP 7.4+ compatibility for broad hosting support; zero external dependencies requirement limits implementation options

### Key Assumptions
- **Market demand exists** for a minimalist PHP microframework positioned between raw PHP and heavy frameworks
- **Developer willingness to trade advanced features for simplicity and stability** in microservice contexts
- **Enterprise adoption possible** despite lacking commercial support structure initially
- **Community will contribute** middleware, documentation, and ecosystem packages once core framework proves valuable
- **Microservices architecture trend continues** driving demand for lightweight, focused service frameworks
- **PHP remains relevant** for backend microservices despite competition from Go, Node.js, and other languages
- **Hosting providers will support** and potentially optimize for lightweight PHP frameworks

## Risks & Open Questions

### Key Risks
- **Market Saturation:** Existing micro-frameworks (Slim, FastRoute, etc.) may have sufficient market share, making differentiation difficult despite technical advantages
- **Community Building:** Without initial funding or marketing, achieving critical mass for community contributions and ecosystem development may take longer than projected
- **Enterprise Hesitation:** Organizations may prefer frameworks with commercial support, comprehensive documentation, and established track records over newer solutions
- **Feature Pressure:** Community demands for advanced features could compromise the minimalist philosophy and zero-dependency commitment
- **Maintenance Burden:** Long-term stability promise creates ongoing responsibility for security updates and PHP version compatibility
- **Competing Ecosystems:** Developers may choose newer languages (Go, Rust) for microservices rather than staying with PHP-based solutions

### Open Questions
- What specific middleware patterns and examples should be prioritized in initial documentation to drive adoption?
- How can we measure and communicate the "decades without upgrades" value proposition effectively to enterprise decision-makers?
- What partnerships or endorsements would accelerate community adoption and credibility?
- Should PHP 8+ specific features be leveraged in separate versions, or maintain single codebase compatibility?
- How do we balance feature requests while preserving core minimalist principles?
- What hosting environments should be prioritized for optimization and testing?

### Areas Needing Further Research
- **Competitive Analysis:** Deep dive into Slim Framework, FastRoute, and other micro-frameworks to identify specific differentiation opportunities
- **Enterprise Use Cases:** Interview potential enterprise users about microservice framework selection criteria and decision processes
- **Performance Benchmarking:** Establish baseline performance metrics against popular alternatives to validate efficiency claims

## Next Steps

### Immediate Actions
1. **Complete competitive analysis** of Slim Framework, FastRoute, and similar micro-frameworks to validate differentiation strategy
2. **Create comprehensive documentation** including quick start guide, middleware examples, and deployment patterns
3. **Establish performance benchmarks** against Laravel and Slim to quantify efficiency claims
4. **Build initial community** through PHP forums, Reddit, and developer communities with compelling use cases
5. **Create companion dev tools package** for scaffolding and rapid setup to improve developer experience
6. **Develop enterprise case studies** and success stories to address adoption concerns

### PM Handoff
This Project Brief provides the full context for MicroApp - PHP Microframework. Please start in 'PRD Generation Mode', review the brief thoroughly to work with the user to create the PRD section by section as the template indicates, asking for any necessary clarification or suggesting improvements.
