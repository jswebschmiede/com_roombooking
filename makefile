ROOT_DIR = .
ADMIN_DIR = src/administrator/components/com_roombooking
VENDOR_DIR = vendor
DEPENDENCIES_DIR = $(ADMIN_DIR)/dependencies

# Main targets
.PHONY: all
all: install scope

# Install dependencies
.PHONY: install
install:
	composer install

# Run PHP-Scoper and move files
.PHONY: scope
scope:
	php-scoper add-prefix --output-dir=$(DEPENDENCIES_DIR) --working-dir=$(ROOT_DIR)

# Clean up
.PHONY: clean
clean:
	rm -rf $(VENDOR_DIR)
	rm -rf $(DEPENDENCIES_DIR) 