delete product_env_service_host.*
	from product_env_service_host, env
	where product_env_service_host.env_id=env.id and env.name='production';
