default:
	@echo "Hello!"

# Local install without MySQL
local:
	sh src/build/setup.sh local ${product}

# Local install with MySQL
localdb:
	sh src/build/setup.sh localdb

# talos-dev.mode.com
dev:
	sh src/build/setup.sh dev

# talos-dev.mode.com
dev2:
	sh src/build/setup.sh dev2

# talos-staging.mode.com
staging:
	sh src/build/setup.sh staging
