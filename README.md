# Newoffers Messenger

Newoffers Messenger is a Pub/Sub messaging provider using aws SNS


### Releases

This project use  [Semantic Versioning](http://semver.org/) for manage releases.

#### Patch

Patch version Z (x.y.Z | x > 0) MUST be incremented if only backwards compatible bug fixes are introduced. A bug fix is defined as an internal change that fixes incorrect behavior.

 ```console
 $ php ./RMT release --type="patch" --label="none" --comment="Release patch
 ```


 #### Minor

 Minor version Y (x.Y.z | x > 0) MUST be incremented if new, backwards compatible functionality is introduced to the public API. It MUST be incremented if any public API functionality is marked as deprecated. It MAY be incremented if substantial new functionality or improvements are introduced within the private code. It MAY include patch level changes. Patch version MUST be reset to 0 when minor version is incremented.

  ```console
  $ php ./RMT release --type="minor" --label="none" --comment="Release minor"
  ```


  #### Major

  Major version X (X.y.z | X > 0) MUST be incremented if any backwards incompatible changes are introduced to the public API. It MAY include minor and patch level changes. Patch and minor version MUST be reset to 0 when major version is incremented.

   ```console
   $ php ./RMT release --type="major" --label="none" --comment="Release major
   ```

