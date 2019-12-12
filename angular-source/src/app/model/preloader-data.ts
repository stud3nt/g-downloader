export class PreloaderData {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	public visible: boolean = false;

	// percentage progress status
	public progress: number = 0;

	// auto close after finish (progress = 100)
	public autoClose: boolean = true;

	// description under loader
	public description: string = null;

	// check progress by api calls (true|false)
	public checkProgressFromApi: boolean = false;

	public reset() {
		this.visible = false;
		this.progress = 0;
		this.autoClose = true;
		this.description = null;
	}
}