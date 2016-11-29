echo "delete host.* from host,env,product_env_service_host pesh where env.name='talos-yoko-test' and pesh.env_id=env.id and pesh.host_id=host.id" | mysql -u prod -pprod inventory
echo "delete pesh.* from env,product_env_service_host pesh where env.name='talos-yoko-test' and pesh.env_id=env.id" | mysql -u prod -pprod inventory
echo "delete from env where name='talos-yoko-test'" | mysql -u prod -pprod inventory
echo "Done"
