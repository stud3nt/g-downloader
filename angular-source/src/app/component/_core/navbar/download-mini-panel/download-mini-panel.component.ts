import { Component, OnInit } from '@angular/core';
import { ParsedFile } from "../../../../model/parsed-file";
import { DownloaderStatus } from "../../../../enum/downloader-status";
import { DownloaderService } from "../../../../service/downloader.service";
import { DownloaderDataService } from "../../../../service/data/downloader-data.service";
import { JsonResponse } from "../../../../model/json-response";
import { RouterService } from "../../../../service/router.service";
import { FileType } from "../../../../enum/file-type";

@Component({
	selector: 'app-download-mini-panel',
	templateUrl: './download-mini-panel.component.html'
})
export class DownloadMiniPanelComponent implements OnInit {

	public mainButtonClass: string = 'navbar-main-button inactive';

	public downloaderStatus: string = DownloaderStatus.Idle;

	public queuedFilesCount: number = 0;
	public queuedFilesSize: string = '0 bytes';

	public queuedFiles: ParsedFile[] = [];

	public DownloaderStatus = DownloaderStatus;
	public FileType = FileType;

	private checkStatusTimeout = null;
	private downloadAction: boolean = false;

	constructor(
		protected downloaderService: DownloaderService,
		protected downloaderDataService: DownloaderDataService,
		public routerService: RouterService
	) { }

	ngOnInit() {
		this.checkStatus();
		this.downloaderDataService.status.subscribe((status) => {
			if (status === this.downloaderStatus)
				return;

			switch (status) {
				case DownloaderStatus.Idle:
					this.start();
					break;

				case DownloaderStatus.Downloading:
					this.stop();
					break;
			}
		});

		// change files list event (e.q. adds or removes
		this.downloaderDataService.touchEvent.subscribe((ms) => {
			clearTimeout(this.checkStatusTimeout);

			this.checkStatusTimeout = setTimeout(() => {
				this.checkStatus();
			}, 1500);
		});
	}

	/**
	 * Starts downloading files process
	 */
	public start(): void {
		this.downloaderService.setDownloaderStatus(
			DownloaderStatus.Downloading
		).subscribe((response: JsonResponse) => {
			if (response.success()) {
				this.downloaderStatus = DownloaderStatus.Downloading;
				this.download();
			}
		});
	}

	/**
	 * Stops downloading files process
	 */
	public stop(): void {
		this.downloaderService.setDownloaderStatus(
			DownloaderStatus.Idle
		).subscribe((response: JsonResponse) => {
			if (response.success()) {
				this.downloaderStatus = DownloaderStatus.Idle;
				this.downloadAction = false;
			}
		});
	}

	/**
	 * Checks download status and statistics
	 */
	public checkStatus(filesOnly: boolean = false): void {
		this.downloaderService.checkDownloaderStatus().subscribe((response: JsonResponse) => {
			this.queuedFiles = [];
			this.queuedFilesCount = parseInt(response.data['queuedFilesCount']);
			this.queuedFilesSize = response.data['queuedFilesSize'];

			if (filesOnly === false)
				this.downloaderStatus = response.data['downloaderStatus'];

			if (response.data['queuedFiles']) {
				for (let queuedFile of response.data['queuedFiles']) {
					this.queuedFiles.push(new ParsedFile(queuedFile));
				}
			}
		});
	}

	/**
	 * Downloading process
	 */
	protected download(): void {
		if (this.downloaderStatus !== DownloaderStatus.Downloading || this.downloadAction === true) {
			return;
		}

		this.downloadAction = true;

		this.downloaderService.downloadProcess().subscribe((response: JsonResponse) => {
			this.checkStatus(true);
			this.downloadAction = false;

			if (this.downloaderStatus === DownloaderStatus.Downloading && response.data.filesCount > 0) {
				this.download();
			} else {
				this.downloaderStatus = DownloaderStatus.Idle;
			}
		});
	}

	protected determineClasses(): void {
		switch (this.downloaderStatus) {
			case DownloaderStatus.Idle:
				this.mainButtonClass = 'navbar-main-button inactive';
				break;

			case DownloaderStatus.Downloading:
				this.mainButtonClass = 'navbar-main-button active';
				break;

			case DownloaderStatus.WaitingForResponse:
				this.mainButtonClass = 'navbar-main-button waiting';
				break;
		}
	}

}
