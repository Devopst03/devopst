try:
    import MySQLdb
except ImportError:
    print "*" * 80
    print
    print "It looks like you're missing the Python MySQLdb library.\n"
    print "To install it: sudo yum install MySQL-python\n"
    print "*" * 80
    raise
import yaml, os.path, sys, types, re, random
import build_query

HERE = os.path.abspath(os.path.split(__file__)[0])

ENTITY_TYPES = ['link', 'dc', 'env', 'host', 'service', 'product']

LOG_SQL = 0

class DBException(Exception): pass
class ValidationError(DBException): pass
class NotFound(DBException): pass
class BadRequest(DBException): pass
class MissingInfo(BadRequest): pass

class Entity:
    def __init__(self, db, item=None, **kw):
        self._db = db
        for k, v in item.items() if item is not None else kw.items():
            setattr(self, k, v)

    def _attrs(self):
        ignore = []
        return [k for k in dir(self) if not callable(getattr(self, k)) and not k.startswith("_") and k not in ignore]

    keys = _attrs

    def items(self):
        ignore = []
        return [(k, getattr(self, k)) for k in dir(self) if not callable(getattr(self, k)) and not k.startswith("_") and k not in ignore]

    def json(self):
        return dict(self.items())

    def __getitem__(self, k):
        return getattr(self, k)

    def __setitem__(self, k, v):
        setattr(self, k, v)

    def __delitem__(self, k):
        delattr(self, k)

    def _reprAttr(self, k):
        v = getattr(self, k)
        if type(v) in (types.StringType, types.UnicodeType): return v.encode("utf-8")
        return `v`

    def __repr__(self):
        fields = ["%s=%s" % (k, self._reprAttr(k)) for k in self._attrs()]
        return "<%s at 0x%x: %s>" % (
            self.__class__,
            id(self),
            ', '.join(fields) if (sum(len(field) for field in fields) < 150) else ',\n\t'.join(fields),
            )

    def delete(self):
        self._db.q("UPDATE %s SET status=0 WHERE id=%s" % (table_from_entity(entity_from_class(self.__class__)), self.id))

class Link(Entity):
    def most_recent_deployment(self):
        return self._db.load_by_sql('deployed_service',
            'SELECT * FROM deployed_service WHERE product_id=%s AND service_id=%s AND env_id=%s AND host_id=%s ORDER BY created_date DESC LIMIT 1',
            (self.product_id, self.service_id, self.env_id, self.host_id))
class DeployedService(Entity): pass
class DC(Entity): pass
class Env(Entity): pass
class Host(Entity): pass
class Service(Entity): pass
class Product(Entity):
    def services(self, env_id, host_name=None):
        if(host_name):
            return [Service(self._db, row) for row in
                self._db.qa("""SELECT service.* FROM product_env_service_host, service, host
                WHERE product_env_service_host.product_id=%s
                AND product_env_service_host.service_id=service.id
                AND product_env_service_host.host_id=host.id
                AND product_env_service_host.status=1
                AND product_env_service_host.env_id=%s
                AND host.name=%s
                GROUP BY product_env_service_host.service_id""", (self.id, env_id, host_name))]
        else:
            return [Service(self._db, row) for row in
                self._db.qa("""SELECT service.* FROM product_env_service_host, service
                WHERE product_env_service_host.product_id=%s
                AND product_env_service_host.service_id=service.id
                AND product_env_service_host.status=1
                AND product_env_service_host.env_id=%s
                GROUP BY product_env_service_host.service_id""", (self.id, env_id))]

    def hosts_by_env_service(self, env_id, service_id):
        return [Host(self._db, row) for row in
            self._db.qa("""SELECT host.* FROM host, product_env_service_host
            WHERE product_env_service_host.status=1
                AND product_env_service_host.product_id=%s
                AND product_env_service_host.env_id=%s
                AND product_env_service_host.service_id=%s
            AND host.status=1 AND product_env_service_host.host_id=host.id""", (self.id, env_id, service_id))]

def table_from_entity(entity):
    if entity not in ENTITY_TYPES:
        raise ValidationError("Unknown entity type %s (valid options are: %s)" % (entity, ", ".join(ENTITY_TYPES)))
    return {
        "dc": "data_center",
        "link": "product_env_service_host",
    }.get(entity, entity)

def class_from_entity(entity):
    return {
        'link': Link,
        'deployed_service': DeployedService,
        'dc': DC,
        'env': Env,
        'host': Host,
        'service': Service,
        'product': Product,
    }[entity]

def entity_from_class(cls):
    return {
        Link: 'link',
        DC: 'dc',
        Env: 'env',
        Host: 'host',
        Service: 'service',
        Product: 'product',
    }[cls]

class NullDB:
    def q(self, sql, vars=None):
        pass
    def q1(self, sql, vars=None):
        return [1]
    def qa(self, sql, vars=None):
        pass
    def insert_id(self):
        return random.randint(1, 9999)

def get_config(env):
    group_vars = yaml.load(open(os.path.join(HERE, '..', '..', 'src', 'playbooks', 'group_vars', 'all')))
    return group_vars['database'].get(env)

class TalosDB:
    def __init__(self, talos_env):
        self.env = talos_env
        config = get_config(self.env)
        assert config, "Database configuration missing for Talos environment %s: check your conf/local.conf and src/playbooks/group_vars_all files" % self.env
        self.db = MySQLdb.connect(host=config['host'], user=config['user'], db=config['name'], passwd=config['password'], autocommit=True)

    # query db, returning a cursor
    def q(self, sql, vars=None):
        if vars is None: vars = []
        c = self.db.cursor(MySQLdb.cursors.DictCursor)
        if LOG_SQL:
            if len(vars):
                print "%s\n\t%s" % (sql, `vars`)
            else:
                print sql
        try:
            c.execute(sql, vars)
        except:
            print "Exception while attempting to execute SQL %s with vars %s" % (sql, `vars`)
            raise
        return c

    # query db, returning the first row
    def q1(self, sql, vars=None):
        return self.q(sql, vars).fetchone()

    # query db, returning all rows
    def qa(self, sql, vars=None):
        return self.q(sql, vars).fetchall()

    # return the last auto_increment id generated
    def insert_id(self):
        return self.q1("SELECT LAST_INSERT_ID() id")['id']

    # perform an old-style talos inventory query, using build_query (which used to be formatquery)
    def query_inventory(self, params, verbose=False):
        bits = build_query.formatquery(params)
        sql = "SELECT %s FROM %s WHERE %s" % (
            bits['tabledata'], bits['tables'], bits['where'])
        if verbose:
            print>>sys.stderr, sql
        return self.qa(sql)

    # helpers to turn dicts into classes

    def class_from_row(self, entity, row):
        if row is None: return None
        return class_from_entity(entity)(self, row)

    def class_from_rows(self, entity, rows):
        if rows is None: return None
        return [class_from_entity(entity)(self, row) for row in rows]

    # various functions to load individual entities

    def load_by_field(self, entity, k, v):
        row = self.q1("SELECT * FROM "+table_from_entity(entity)+" WHERE status=1 AND "+k+"=%s", (v,))
        return self.class_from_row(entity, row)

    def load_all_by_field(self, entity, k, v):
        rows = self.qa("SELECT * FROM "+table_from_entity(entity)+" WHERE status=1 AND "+k+"=%s", (v,))
        return self.class_from_rows(entity, rows)

    def load_by_sql(self, entity, sql, vars=None):
        return self.class_from_row(entity, self.q1(sql, vars))

    def load_all_by_sql(self, entity, sql, vars=None):
        return self.class_from_rows(entity, self.qa(sql, vars))

    def load_by_id(self, entity, _id):
        return self.class_from_row(entity, self.load_by_field(entity, 'id', _id))

    def load_by_name(self, entity, name):
        return self.class_from_row(entity, self.load_by_field(entity, 'name', name))

    def require_by_name(self, entity, name):
        item = self.load_by_name(entity, name)
        if item is None: raise NotFound("Could not load %s with name %s" % (entity, name))
        return item

    def require_one_by_name(self, entity, name):
        items = self.load_all_by_field(entity, 'name', name)
        if not len(items): raise NotFound("Could not load %s with name %s" % (entity, name))
        if len(items) > 1:
            print "Multiple options exist for %s with name=%s:" % (entity, name)
            for item in items:
                print "\t%s" % `item`
            raise MissingInfo("Could not unambiguously resolve your request; specify an item ID instead, from the list above")
        return self.class_from_row(entity, items[0])

    # turn 'product', 'dc' fields into ids
    def lookup_ids(self, meta):
        mapping = {
            'dc': 'data_center_id',
            'product': 'product_id',
            'service': 'service_id',
            'host': 'host_id',
            'env': 'env_id',
        }
        r = {}
        for k, v in meta.items():
            k = re.sub('_name$', '', k)
            if mapping.has_key(k):
                k, v = mapping[k], self.require_one_by_name(k, v).id
            r[k] = v
        return r

    # turn a dict into strings useful for sql INSERT statements
    def prep_col_list(self, meta):
        cols, values = zip(*sorted(meta.items()))
        return (
            ", ".join(cols),
            ", ".join("%s" for x in cols),
            values
        )

    # turn a dict into strings useful for sql UPDATE and SELECT statements
    def prep_kv_cols(self, meta):
        cols, values = zip(*sorted(meta.items()))
        return (
            ["%s=%%s" % col for col in cols],
            values
        )

    # query helper for 'talos show'

    def load(self, entity, meta):
        table = table_from_entity(entity)
        meta = self.lookup_ids(meta)
        meta['status'] = 1

        select_cols = '%s.*' % table
        from_table = table
        group_by = ''

        # figure out if we need to join on product_env_service_host
        join = 0
        if table in ('product', 'env', 'service', 'host'):
            for k in ('product_id', 'env_id', 'service_id', 'host_id'):
                if meta.has_key(k):
                    join = 1
                    break
            if join:
                from_table = '%s JOIN product_env_service_host ON %s.id=product_env_service_host.%s_id' % (table, table, table)
                group_by = 'GROUP BY %s.id' % table
        elif table == 'product_env_service_host':
            # we're pulling links, which means we also need to join on product, env, service, host to get object names
            select_cols = '%(table)s.*, product.name product_name, env.name env_name, service.name service_name, host.name host_name' % {'table': table}
            from_table = '''%(table)s
                JOIN product ON product.id=%(table)s.product_id
                JOIN env ON env.id=%(table)s.env_id
                JOIN service on service.id=%(table)s.service_id
                JOIN host on host.id=%(table)s.host_id''' % {'table': table}
            meta['product.status'] = 1
            meta['env.status'] = 1
            meta['service.status'] = 1
            meta['host.status'] = 1
            join = 1
        if join:
            newmeta = {}
            for k, v in meta.items():
                if k.find(".") != -1:
                    pass
                elif k in ('product_id', 'env_id', 'service_id', 'host_id'):
                    k = 'product_env_service_host.%s' % k
                else:
                    k = '%s.%s' % (table, k)
                newmeta[k] = v
            meta = newmeta

        kv, values = self.prep_kv_cols(meta)
        return self.class_from_rows(entity, self.q(
            "SELECT %s FROM %s WHERE %s %s" % (select_cols, from_table, " AND ".join(kv), group_by),
            values,
        ))

    # add a talos inventory item
    def add_entity(self, entity, meta):
        table = table_from_entity(entity)

        if entity == 'host':
            if sorted(meta.keys()) != ['dc', 'name']: raise MissingInfo("Required fields: name, dc")
            host = self.load_by_name("host", meta['name'])
            assert host is None, "host with name %s already exists, with id %d" % (meta['name'], host.id)
            meta = self.lookup_ids(meta)
        else:
            if meta.keys() != ['name']: raise MissingInfo("Required fields: name")

        cols, values, data = self.prep_col_list(meta)
        sql = "INSERT INTO %s (%s, status, created_date, updated_date) VALUES (%s, 1, now(), now())" % (table, cols, values)
        self.q(sql, data)
        _id = self.insert_id()
        print "Inserted new %s with ID %s (env %s)" % (entity, _id, self.env)

    def _validate_link(self, ids):
        meta = self.lookup_ids(ids)
        keys = sorted(meta.keys())
        if "id" in keys: keys.remove("id")
        if keys == ['product_id', 'service_id']:
            table = 'product_service'
        elif keys == ['env_id', 'host_id', 'product_id', 'service_id']:
            table = 'product_env_service_host'
        else:
            raise BadRequest("I don't know how to link together %s" % " + ".join(keys))
        return (table, meta)

    # link talos inventory items together
    def link_entities(self, ids):
        table, meta = self._validate_link(ids)
        cols, values, data = self.prep_col_list(meta)
        sql = "INSERT INTO %s (%s, status, created_date, updated_date) VALUES (%s, 1, now(), now())" % (table, cols, values)
        self.q(sql, data)
        print "Inserted new %s link with ID %s (env %s)" % (table, self.insert_id(), self.env)

    def unlink_entities(self, ids):
        table, meta = self._validate_link(ids)
        kv, values = self.prep_kv_cols(meta)
        sql = "DELETE FROM %s WHERE %s" % (table, " AND ".join(kv),)
        c = self.q(sql, values)
        print "%d link(s) deleted" % (c.rowcount,)

def connect(talos_env, fallback_to_fake_db=False):
    config = get_config(talos_env)
    if not config:
        if fallback_to_fake_db:
            return NullDB()
        return
    return TalosDB(talos_env)
