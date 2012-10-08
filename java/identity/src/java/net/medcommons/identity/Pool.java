package net.medcommons.identity;

/**
 * A synchronized FIFO.
 *
 * A fixed number of objects can be stored in a Pool.  When the pool is empty,
 * a consumer can wait for a specified time for another thread to put an object
 * into the Pool.
 *
 * The intent is for this to be used for database connections, or worker
 * threads.
 */
public class Pool {

    /** count of objects currently in pool */
    protected int inum;

    /** index into array of next object to get */
    protected int iget;

    /** the pool of objects */
    protected Object[] array;

    /**
     * Create a Pool with a fixed number of objects.
     *
     * @param max  maximum number of objects this Pool can contain
     */
    public Pool(int max) {
	this.inum = this.iget = 0;
	this.array = new Object[max];
    }

    /**
     * Test if Pool is empty
     */
    public synchronized boolean isEmpty() {
	return this.inum == 0;
    }

    /**
     * Try to get an object from the pool.
     *
     * Returns null if the pool is empty.
     */
    public synchronized Object get() {
	return this.inum == 0 ? null : get0();
    }

    /**
     * Wait indefinitely until an object is placed in the pool
     */
    public synchronized Object getWait() throws InterruptedException {
	while (this.inum == 0)
	    wait();

	return get0();
    }

    /**
     * Wait a specified number of milliseconds of real time, if at the end
     * that time the pool is still empty, return null.
     *
     * getWait(0L) is the same as get(): it will return immediately without
     * yielding.  To yield to another thread, try getWait(1L).
     *
     * @see #get
     */
    public synchronized Object getWait(long millis) throws InterruptedException {
	long now = System.currentTimeMillis();
	long then = now + millis;

	while (now < then && this.inum == 0) {
	    wait(then - now);
	    now = System.currentTimeMillis();
	}

	return get();
    }

    /**
     * pre: !isEmpty()
     */
    private Object get0() {
	int i = this.iget;

	this.iget = (this.iget + 1) % this.array.length;
	this.inum--;

	return this.array[i];
    }

    /**
     * Test if Pool is full.
     */
    public synchronized boolean isFull() {
	return this.inum == this.array.length;
    }

    /**
     * Return an object to the pool.
     *
     * If the pool is full, this returns false.
     */
    public synchronized boolean put(Object o) {
	int length = this.array.length;

	if (this.inum < length) {
	    int i = (this.iget + this.inum) % length;
	    int j = this.inum++;

	    this.array[i] = o;

	    if (j == 0) notify();

	    return true;
	}
	else
	    return false;
    }

}
